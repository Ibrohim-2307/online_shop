<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentAdapterInterface;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Ledger\SellerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DemoPaymentAdapter implements PaymentAdapterInterface
{
    protected string $secretKey;

    public function __construct(
        protected SellerLedgerService $ledgerService
    ) {
        $this->secretKey = config('services.payment.demo_secret', 'marketplace-sandbox-hmac-secret-key-2026');
    }

    public function getProviderName(): string
    {
        return 'demo_sandbox';
    }

    /**
     * Initialize payment session for an order.
     * Generates a payment record and returns sandbox redirect URL.
     * Note: Redirect NEVER marks an order as paid.
     */
    public function initializePayment(Order $order, array $options = []): array
    {
        $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $this->getProviderName(),
            'transaction_id' => $transactionId,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'status' => 'pending',
            'payload_snapshot' => $options,
        ]);

        $signature = hash_hmac('sha256', "{$order->id}:{$order->grand_total}:{$transactionId}", $this->secretKey);

        return [
            'payment_id' => $payment->id,
            'transaction_id' => $transactionId,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'payment_url' => url("/payments/demo-checkout?order={$order->id}&txn={$transactionId}&sig={$signature}"),
            'status' => 'pending',
        ];
    }

    /**
     * Compute HMAC-SHA256 signature for payload verification.
     */
    public function computeSignature(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, $this->secretKey);
    }

    /**
     * Verify incoming webhook from request and process payment.
     */
    public function verifyPayment(Request $request): array
    {
        $rawContent = $request->getContent();
        $signature = $request->header('X-Payment-Signature') ?? $request->input('signature');

        $payload = $request->isJson() ? $request->json()->all() : $request->all();

        return $this->processWebhook($payload, (string) $signature, $rawContent);
    }

    /**
     * Process signed payment webhook with idempotency and double-entry ledger settlement.
     */
    public function processWebhook(array $payload, string $signature, string $rawBody = ''): array
    {
        $eventId = $payload['event_id'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $amount = (int) ($payload['amount'] ?? 0);

        if (!$eventId || !$transactionId || !$orderId) {
            throw new InvalidArgumentException("Webhook payload yetarli emas (event_id, transaction_id, order_id majburiy).");
        }

        // 1. Verify Cryptographic Signature
        $expectedSignature = hash_hmac('sha256', "{$eventId}:{$transactionId}:{$orderId}:{$amount}", $this->secretKey);
        if (!hash_equals($expectedSignature, $signature)) {
            // Check fallback with raw content if provided
            $altSignature = $this->computeSignature($rawBody ?: json_encode($payload));
            if (!hash_equals($altSignature, $signature)) {
                throw new InvalidArgumentException("Webhook imzosi yaroqsiz (Invalid HMAC Signature).");
            }
        }

        // 2. Idempotency Check: Event Uniqueness
        $existingEvent = WebhookEvent::where('provider', $this->getProviderName())
            ->where('event_id', $eventId)
            ->first();

        if ($existingEvent) {
            return [
                'status' => 'already_processed',
                'message' => 'Ushbu webhook xabarnomasi avval qayta ishlangan.',
                'order_id' => $orderId,
            ];
        }

        // 3. Atomic Database Settlement
        return DB::transaction(function () use ($eventId, $transactionId, $orderId, $amount, $signature, $payload) {
            $payment = Payment::where('transaction_id', $transactionId)
                ->where('order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                throw new InvalidArgumentException("Tizimda mos keluvchi to'lov topilmadi: {$transactionId}.");
            }

            // Verify amount matching
            if ($payment->amount !== $amount) {
                throw new InvalidArgumentException(
                    "To'lov summasi mos kelmadi. Kutilgan: {$payment->amount}, yuborilgan: {$amount}."
                );
            }

            // If payment is already paid (e.g. from prior concurrent execution), safely return
            if ($payment->status === 'paid') {
                return [
                    'status' => 'already_paid',
                    'message' => 'To\'lov avval tasdiqlangan.',
                    'order_id' => $orderId,
                ];
            }

            // Mark Payment as Paid
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payload_snapshot' => $payload,
            ]);

            // Update Parent Order
            $order = Order::with('suborders')->findOrFail($orderId);
            $order->update(['status' => 'processing']);

            // Commit Inventory Reservations (Reserved -> Committed)
            InventoryReservation::where('order_id', $order->id)
                ->where('status', 'reserved')
                ->update(['status' => 'committed']);

            // Update Suborders and Record Seller Ledger Credits
            foreach ($order->suborders as $suborder) {
                $suborder->update(['status' => 'confirmed']);

                // Settle Seller Ledger
                $this->ledgerService->recordOrderSale($suborder);
            }

            // Record Transactional Outbox Event
            OutboxEvent::create([
                'event_type' => 'PaymentCompleted',
                'aggregate_type' => 'Payment',
                'aggregate_id' => $payment->id,
                'payload' => [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $payment->amount,
                    'transaction_id' => $payment->transaction_id,
                ],
                'status' => 'pending',
            ]);

            // Save Webhook Event record
            WebhookEvent::create([
                'provider' => $this->getProviderName(),
                'event_id' => $eventId,
                'signature' => $signature,
                'payload' => $payload,
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return [
                'status' => 'success',
                'message' => 'To\'lov muvaffaqiyatli tasdiqlandi va seller hisobiga yozildi.',
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ];
        });
    }

    /**
     * Process refund via payment provider.
     */
    public function processRefund(Order $order, int $amount, string $reason): array
    {
        return [
            'status' => 'refund_processed',
            'order_id' => $order->id,
            'refund_amount' => $amount,
            'provider' => $this->getProviderName(),
        ];
    }
}
