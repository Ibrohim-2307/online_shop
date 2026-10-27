<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager
    ) {}

    /**
     * Initiate payment session for an order.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'nullable|string|in:demo_sandbox,demo,payme,click,uzum,cash_on_delivery',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Check ownership if user is authenticated
        if ($request->user() && $order->user_id && $order->user_id !== $request->user()->id) {
            return ApiResponse::error("Ushbu buyurtmaga to'lov qilish ruxsati yo'q.", 'UNAUTHORIZED_ORDER_ACCESS', [], 403);
        }

        if (!in_array($order->status, ['pending_payment', 'pending', 'awaiting_payment'])) {
            return ApiResponse::error(
                "Buyurtma to'lov holatida emas (Joriy holat: {$order->status}).",
                'INVALID_ORDER_STATE',
                [],
                422
            );
        }

        // Ton freight shipping negotiation check
        try {
            app(\App\Services\Rfq\RfqService::class)->validateFreightShippingForPayment($order);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'TON_FREIGHT_SHIPPING_REQUIRED', [], 422);
        }

        $defaultDriver = app()->environment('testing') ? 'demo_sandbox' : config('services.payment.default', 'click');
        $method = $validated['payment_method'] ?? $defaultDriver;

        if (!$this->paymentManager->isProviderConfigured($method)) {
            return ApiResponse::error(
                "Tanlangan to'lov usuli ({$method}) hozirda faol emas yoki sozlanmagan.",
                'PROVIDER_NOT_CONFIGURED',
                [],
                422
            );
        }

        try {
            $driver = $this->paymentManager->driver($method);
            $session = $driver->initializePayment($order, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $method,
            ]);

            return ApiResponse::success($session, "To'lov sessiyasi muvaffaqiyatli yaratildi.");
        } catch (\Throwable $e) {
            return ApiResponse::error("To'lov sessiyasini yaratishda xato: " . $e->getMessage(), 'PAYMENT_INITIATION_FAILED', [], 500);
        }
    }

    /**
     * Inbound signed webhook handler for payment providers.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $result = $this->paymentManager->demo()->verifyPayment($request);
            return ApiResponse::success($result, 'Webhook qabul qilindi.');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'WEBHOOK_VERIFICATION_FAILED', [], 400);
        } catch (\Throwable $e) {
            return ApiResponse::error('To\'lovni qayta ishlashda kutilmagan xato: ' . $e->getMessage(), 'PAYMENT_PROCESSING_ERROR', [], 500);
        }
    }

    /**
     * Check payment status for an order.
     */
    public function status(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        if ($request->user() && $order->user_id && $order->user_id !== $request->user()->id) {
            return ApiResponse::error("Ruxsat yo'q.", 'UNAUTHORIZED_ACCESS', [], 403);
        }

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        return ApiResponse::success([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_status' => $order->status,
            'payment_status' => $payment?->status ?? 'unpaid',
            'transaction_id' => $payment?->transaction_id,
            'paid_at' => $payment?->paid_at,
            'amount' => $payment?->amount ?? $order->grand_total,
            'currency' => $order->currency,
        ]);
    }
}
