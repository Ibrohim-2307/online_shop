<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentAdapterInterface;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\Payment;
use App\Models\PaymeTransaction;
use App\Services\Ledger\SellerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymePaymentService implements PaymentAdapterInterface
{
    protected ?string $merchantId;
    protected ?string $secretKey;

    public function __construct(
        protected SellerLedgerService $ledgerService
    ) {
        $this->merchantId = config('services.payme.merchant_id');
        $this->secretKey = config('services.payme.secret_key');
    }

    public function getProviderName(): string
    {
        return 'payme';
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->secretKey);
    }

    /**
     * Generate Payme checkout URL.
     */
    public function initializePayment(Order $order, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new InvalidArgumentException("Payme to'lov tizimi sozlanmagan (Merchant ID yoki Maxfiy Kalit kiritilmagan).");
        }

        $amountInTiyin = (int) ($order->grand_total * 100);
        $paymeParams = "m={$this->merchantId};ac.order_id={$order->id};a={$amountInTiyin}";
        $paymentUrl = "https://checkout.paycom.uz/" . base64_encode($paymeParams);

        return [
            'provider' => 'payme',
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'payment_url' => $paymentUrl,
            'status' => 'pending',
        ];
    }

    /**
     * Check HTTP Basic Authentication from Payme.
     */
    public function checkAuth(Request $request): bool
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $credentials = base64_decode(substr($authHeader, 6));
        if (!$credentials || !str_contains($credentials, ':')) {
            return false;
        }

        [$login, $key] = explode(':', $credentials, 2);

        return hash_equals('Paycom', $login) && hash_equals((string) $this->secretKey, (string) $key);
    }

    /**
     * Process Payme JSON-RPC 2.0 Request.
     */
    public function handleJsonRpc(Request $request): array
    {
        if (!$this->checkAuth($request)) {
            return [
                'error' => [
                    'code' => -32504,
                    'message' => 'Insufficient privilege (Avtorizatsiya xatosi)',
                ],
                'id' => $request->input('id'),
            ];
        }

        $method = $request->input('method');
        $params = $request->input('params', []);
        $reqId = $request->input('id');

        try {
            $result = match ($method) {
                'CheckPerformTransaction' => $this->checkPerformTransaction($params),
                'CreateTransaction' => $this->createTransaction($params),
                'PerformTransaction' => $this->performTransaction($params),
                'CancelTransaction' => $this->cancelTransaction($params),
                'CheckTransaction' => $this->checkTransaction($params),
                default => throw new InvalidArgumentException("Noma'lum metod: {$method}"),
            };

            return [
                'result' => $result,
                'id' => $reqId,
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'error' => [
                    'code' => (int) ($e->getCode() ?: -31008),
                    'message' => $e->getMessage(),
                ],
                'id' => $reqId,
            ];
        }
    }

    public function verifyPayment(Request $request): array
    {
        return $this->handleJsonRpc($request);
    }

    protected function findOrder(array $params): Order
    {
        $orderId = $params['account']['order_id'] ?? $params['account']['order_number'] ?? null;
        if (!$orderId) {
            throw new InvalidArgumentException("Buyurtma ko'rsatilmagan (account.order_id kerak)", -31001);
        }

        $order = is_numeric($orderId)
            ? Order::find((int) $orderId)
            : Order::where('order_number', $orderId)->first();

        if (!$order) {
            throw new InvalidArgumentException("Buyurtma topilmadi", -31001);
        }

        return $order;
    }

    protected function checkPerformTransaction(array $params): array
    {
        $order = $this->findOrder($params);
        $amount = (int) ($params['amount'] ?? 0);
        $expectedAmountInTiyin = (int) ($order->grand_total * 100);

        if ($amount !== $expectedAmountInTiyin) {
            throw new InvalidArgumentException("To'lov summasi noto'g'ri (tiyinlarda)", -31008);
        }

        if ($order->status === 'completed' || $order->status === 'processing') {
            $payment = Payment::where('order_id', $order->id)->where('status', 'paid')->first();
            if ($payment) {
                throw new InvalidArgumentException("Buyurtma allaqachon to'langan", -31008);
            }
        }

        return ['allow' => true];
    }

    protected function createTransaction(array $params): array
    {
        $paymeId = (string) ($params['id'] ?? '');
        $paymeTime = (int) ($params['time'] ?? 0);
        $amount = (int) ($params['amount'] ?? 0);

        $existing = PaymeTransaction::where('payme_trans_id', $paymeId)->first();
        if ($existing) {
            return [
                'create_time' => (int) $existing->create_time,
                'transaction' => (string) $existing->id,
                'state' => (int) $existing->state,
            ];
        }

        // Validate order and amount
        $order = $this->findOrder($params);
        $expectedAmountInTiyin = (int) ($order->grand_total * 100);
        if ($amount !== $expectedAmountInTiyin) {
            throw new InvalidArgumentException("To'lov summasi noto'g'ri (tiyinlarda)", -31008);
        }

        $nowMs = (int) round(microtime(true) * 1000);

        $transaction = PaymeTransaction::create([
            'order_id' => $order->id,
            'payme_trans_id' => $paymeId,
            'amount' => $amount,
            'state' => 1,
            'payme_time' => $paymeTime,
            'create_time' => $nowMs,
        ]);

        return [
            'create_time' => $nowMs,
            'transaction' => (string) $transaction->id,
            'state' => 1,
        ];
    }

    protected function performTransaction(array $params): array
    {
        $paymeId = (string) ($params['id'] ?? '');
        $transaction = PaymeTransaction::where('payme_trans_id', $paymeId)->first();

        if (!$transaction) {
            throw new InvalidArgumentException("Tranzaksiya topilmadi", -31003);
        }

        if ($transaction->state === 2) {
            return [
                'transaction' => (string) $transaction->id,
                'perform_time' => (int) $transaction->perform_time,
                'state' => 2,
            ];
        }

        if ($transaction->state !== 1) {
            throw new InvalidArgumentException("Tranzaksiya bekor qilingan yoki yakunlangan", -31008);
        }

        return DB::transaction(function () use ($transaction) {
            $nowMs = (int) round(microtime(true) * 1000);
            $transaction->update([
                'state' => 2,
                'perform_time' => $nowMs,
            ]);

            $order = Order::findOrFail($transaction->order_id);

            // Update/Create Payment
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'amount' => $order->grand_total,
                    'currency' => $order->currency,
                ]
            );

            $payment->update([
                'payment_method' => 'payme',
                'transaction_id' => 'PAYME-' . $transaction->payme_trans_id,
                'status' => 'paid',
                'paid_at' => now(),
                'payload_snapshot' => [
                    'payme_trans_id' => $transaction->payme_trans_id,
                    'payme_time' => $transaction->payme_time,
                ],
            ]);

            $order->update(['status' => 'processing']);

            // Commit inventory reservations
            InventoryReservation::where('order_id', $order->id)
                ->where('status', 'reserved')
                ->update(['status' => 'committed']);

            // Settle suborders and credit seller ledger
            foreach ($order->suborders as $suborder) {
                $suborder->update(['status' => 'confirmed']);
                $this->ledgerService->recordOrderSale($suborder);
            }

            OutboxEvent::create([
                'event_type' => 'PaymentCompleted',
                'aggregate_type' => 'Payment',
                'aggregate_id' => $payment->id,
                'payload' => [
                    'provider' => 'payme',
                    'order_id' => $order->id,
                    'amount' => $order->grand_total,
                ],
            ]);

            return [
                'transaction' => (string) $transaction->id,
                'perform_time' => $nowMs,
                'state' => 2,
            ];
        });
    }

    protected function cancelTransaction(array $params): array
    {
        $paymeId = (string) ($params['id'] ?? '');
        $reason = (int) ($params['reason'] ?? 1);

        $transaction = PaymeTransaction::where('payme_trans_id', $paymeId)->first();
        if (!$transaction) {
            throw new InvalidArgumentException("Tranzaksiya topilmadi", -31003);
        }

        $nowMs = (int) round(microtime(true) * 1000);

        if ($transaction->state === 1) {
            $transaction->update([
                'state' => -1,
                'cancel_time' => $nowMs,
                'reason' => $reason,
            ]);
        } elseif ($transaction->state === 2) {
            $transaction->update([
                'state' => -2,
                'cancel_time' => $nowMs,
                'reason' => $reason,
            ]);
        }

        return [
            'transaction' => (string) $transaction->id,
            'cancel_time' => $nowMs,
            'state' => (int) $transaction->state,
        ];
    }

    protected function checkTransaction(array $params): array
    {
        $paymeId = (string) ($params['id'] ?? '');
        $transaction = PaymeTransaction::where('payme_trans_id', $paymeId)->first();

        if (!$transaction) {
            throw new InvalidArgumentException("Tranzaksiya topilmadi", -31003);
        }

        return [
            'create_time' => (int) $transaction->create_time,
            'perform_time' => (int) ($transaction->perform_time ?? 0),
            'cancel_time' => (int) ($transaction->cancel_time ?? 0),
            'transaction' => (string) $transaction->id,
            'state' => (int) $transaction->state,
            'reason' => $transaction->reason,
        ];
    }

    public function processRefund(Order $order, int $amount, string $reason): array
    {
        $transaction = PaymeTransaction::where('order_id', $order->id)
            ->where('state', 2)
            ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Payme tranzaksiyasi topilmadi.',
            ];
        }

        return $this->cancelTransaction([
            'id' => $transaction->payme_trans_id,
            'reason' => 5,
        ]);
    }
}
