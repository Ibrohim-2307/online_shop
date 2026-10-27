<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentAdapterInterface;
use App\Models\ClickTransaction;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\Payment;
use App\Services\Ledger\SellerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClickPaymentService implements PaymentAdapterInterface
{
    protected ?string $serviceId;
    protected ?string $merchantId;
    protected ?string $secretKey;

    public function __construct(
        protected SellerLedgerService $ledgerService
    ) {
        $this->serviceId = config('services.click.service_id');
        $this->merchantId = config('services.click.merchant_id');
        $this->secretKey = config('services.click.secret_key');
    }

    public function getProviderName(): string
    {
        return 'click';
    }

    public function isConfigured(): bool
    {
        return !empty($this->serviceId) && !empty($this->merchantId) && !empty($this->secretKey);
    }

    /**
     * Generate Click payment checkout URL for an order.
     */
    public function initializePayment(Order $order, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new InvalidArgumentException("Click to'lov tizimi sozlanmagan (Service ID yoki Maxfiy Kalit kiritilmagan).");
        }

        $returnUrl = $options['return_url'] ?? route('order.success', ['orderNumber' => $order->order_number]);
        $paymentUrl = "https://my.click.uz/services/pay?service_id={$this->serviceId}&merchant_id={$this->merchantId}&amount={$order->grand_total}&transaction_param={$order->id}&return_url=" . urlencode($returnUrl);

        return [
            'provider' => 'click',
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'payment_url' => $paymentUrl,
            'status' => 'pending',
        ];
    }

    /**
     * Handle incoming Click Prepare or Complete request.
     */
    public function handleCallback(Request $request): array
    {
        $action = (int) $request->input('action', 0);

        if ($action === 0) {
            return $this->handlePrepare($request);
        } elseif ($action === 1) {
            return $this->handleComplete($request);
        }

        return [
            'error' => -3,
            'error_note' => 'Action not found',
        ];
    }

    public function verifyPayment(Request $request): array
    {
        return $this->handleCallback($request);
    }

    /**
     * 1. Prepare stage: Verify order and amount.
     */
    public function handlePrepare(Request $request): array
    {
        $clickTransId = (string) $request->input('click_trans_id');
        $serviceId = (string) $request->input('service_id');
        $merchantTransId = (string) $request->input('merchant_trans_id');
        $rawAmount = (string) $request->input('amount');
        $amount = (float) $rawAmount;
        $action = (int) $request->input('action', 0);
        $signTime = (string) $request->input('sign_time');
        $signString = (string) $request->input('sign_string');

        // Check signature (supports both exact raw string and standard float string)
        $sign1 = md5($clickTransId . $serviceId . $this->secretKey . $merchantTransId . $rawAmount . $action . $signTime);
        $sign2 = md5($clickTransId . $serviceId . $this->secretKey . $merchantTransId . $amount . $action . $signTime);

        if (strtolower($sign1) !== strtolower($signString) && strtolower($sign2) !== strtolower($signString)) {
            return [
                'error' => -1,
                'error_note' => 'SIGN CHECK FAILED',
            ];
        }

        // Find Order by ID or order_number
        $order = is_numeric($merchantTransId)
            ? Order::find((int) $merchantTransId)
            : Order::where('order_number', $merchantTransId)->first();

        if (!$order) {
            return [
                'error' => -5,
                'error_note' => 'User does not exist',
            ];
        }

        // Check already paid
        if ($order->status === 'completed' || $order->status === 'processing') {
            $payment = Payment::where('order_id', $order->id)->where('status', 'paid')->first();
            if ($payment) {
                return [
                    'error' => -4,
                    'error_note' => 'Already paid',
                ];
            }
        }

        // Validate amount
        if (round((float) $order->grand_total, 2) !== round($amount, 2)) {
            return [
                'error' => -2,
                'error_note' => 'Incorrect parameter amount',
            ];
        }

        // Record or update Click Transaction
        $clickTrans = ClickTransaction::updateOrCreate(
            ['click_trans_id' => $clickTransId],
            [
                'order_id' => $order->id,
                'service_id' => $serviceId,
                'merchant_trans_id' => (string) $order->id,
                'amount' => (int) $order->grand_total,
                'action' => 0,
                'sign_time' => $signTime,
                'status' => 'prepared',
            ]
        );

        return [
            'click_trans_id' => $clickTransId,
            'merchant_trans_id' => (string) $order->id,
            'merchant_prepare_id' => $clickTrans->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }

    /**
     * 2. Complete stage: Confirm payment and atomically settle.
     */
    public function handleComplete(Request $request): array
    {
        $clickTransId = (string) $request->input('click_trans_id');
        $serviceId = (string) $request->input('service_id');
        $clickPaydocId = (string) $request->input('click_paydoc_id');
        $merchantTransId = (string) $request->input('merchant_trans_id');
        $merchantPrepareId = (string) $request->input('merchant_prepare_id');
        $rawAmount = (string) $request->input('amount');
        $amount = (float) $rawAmount;
        $action = (int) $request->input('action', 1);
        $error = (int) $request->input('error', 0);
        $errorNote = (string) $request->input('error_note', '');
        $signTime = (string) $request->input('sign_time');
        $signString = (string) $request->input('sign_string');

        // Check signature for Complete (supports both exact raw string and standard float string)
        $sign1 = md5($clickTransId . $serviceId . $this->secretKey . $merchantTransId . $merchantPrepareId . $rawAmount . $action . $signTime);
        $sign2 = md5($clickTransId . $serviceId . $this->secretKey . $merchantTransId . $merchantPrepareId . $amount . $action . $signTime);

        if (strtolower($sign1) !== strtolower($signString) && strtolower($sign2) !== strtolower($signString)) {
            return [
                'error' => -1,
                'error_note' => 'SIGN CHECK FAILED',
            ];
        }

        $order = is_numeric($merchantTransId)
            ? Order::find((int) $merchantTransId)
            : Order::where('order_number', $merchantTransId)->first();

        if (!$order) {
            return [
                'error' => -5,
                'error_note' => 'User does not exist',
            ];
        }

        $clickTrans = ClickTransaction::where('click_trans_id', $clickTransId)->first();
        if (!$clickTrans) {
            return [
                'error' => -6,
                'error_note' => 'Transaction does not exist',
            ];
        }

        // If Click reports error, mark failed
        if ($error < 0) {
            $clickTrans->update([
                'status' => 'failed',
                'error' => $error,
                'error_note' => $errorNote,
            ]);

            return [
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => (string) $order->id,
                'error' => $error,
                'error_note' => $errorNote,
            ];
        }

        // Check if already paid (idempotency)
        if ($clickTrans->status === 'paid') {
            return [
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => (string) $order->id,
                'merchant_confirm_id' => $clickTrans->id,
                'error' => 0,
                'error_note' => 'Success',
            ];
        }

        // Atomic settlement
        return DB::transaction(function () use ($clickTrans, $order, $clickTransId, $clickPaydocId, $error, $errorNote) {
            $clickTrans->update([
                'click_paydoc_id' => $clickPaydocId,
                'action' => 1,
                'status' => 'paid',
                'error' => $error,
                'error_note' => $errorNote,
            ]);

            // Update or create payment record
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'amount' => $order->grand_total,
                    'currency' => $order->currency,
                ]
            );

            $payment->update([
                'payment_method' => 'click',
                'transaction_id' => 'CLICK-' . $clickTransId,
                'status' => 'paid',
                'paid_at' => now(),
                'payload_snapshot' => [
                    'click_trans_id' => $clickTransId,
                    'click_paydoc_id' => $clickPaydocId,
                ],
            ]);

            // Order status to processing
            $order->update(['status' => 'processing']);

            // Commit inventory reservations
            InventoryReservation::where('order_id', $order->id)
                ->where('status', 'reserved')
                ->update(['status' => 'committed']);

            // Update suborders and record ledger credits
            foreach ($order->suborders as $suborder) {
                $suborder->update(['status' => 'confirmed']);
                $this->ledgerService->recordOrderSale($suborder);
            }

            OutboxEvent::create([
                'event_type' => 'PaymentCompleted',
                'aggregate_type' => 'Payment',
                'aggregate_id' => $payment->id,
                'payload' => [
                    'provider' => 'click',
                    'order_id' => $order->id,
                    'amount' => $order->grand_total,
                ],
            ]);

            return [
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => (string) $order->id,
                'merchant_confirm_id' => $clickTrans->id,
                'error' => 0,
                'error_note' => 'Success',
            ];
        });
    }

    public function processRefund(Order $order, int $amount, string $reason): array
    {
        return [
            'success' => false,
            'message' => 'Click orqali to\'lovni bekor qilish merchant kabineti orqali amalga oshiriladi.',
        ];
    }
}
