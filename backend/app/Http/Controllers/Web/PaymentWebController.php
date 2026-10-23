<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\DemoPaymentAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentWebController extends Controller
{
    public function __construct(
        protected DemoPaymentAdapter $paymentAdapter
    ) {}

    /**
     * Dedicated payment checkout screen for Click, Payme, and Cash on Delivery.
     */
    public function checkout(Request $request, string $orderNumber): View|RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['payments', 'suborders.seller', 'suborders.items'])
            ->firstOrFail();

        // If order already paid or completed
        if ($order->status === 'completed' || ($order->latest_payment && $order->latest_payment->status === 'paid')) {
            return redirect()->route('order.success', ['orderNumber' => $order->order_number])
                ->with('info', "Ushbu buyurtma uchun to'lov allaqachon to'liq amalga oshirilgan.");
        }

        $payment = $order->latest_payment;
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'click',
                'transaction_id' => 'TXN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                'amount' => $order->grand_total,
                'currency' => $order->currency,
                'status' => 'pending',
            ]);
        }

        // Generate Click URL
        $clickServiceId = config('services.click.service_id');
        $clickMerchantId = config('services.click.merchant_id');
        $clickUrl = null;
        if ($clickServiceId && $clickMerchantId) {
            $returnUrl = route('order.success', ['orderNumber' => $order->order_number]);
            $clickUrl = "https://my.click.uz/services/pay?service_id={$clickServiceId}&merchant_id={$clickMerchantId}&amount={$order->grand_total}&transaction_param={$order->id}&return_url=" . urlencode($returnUrl);
        }

        // Generate Payme URL
        $paymeMerchantId = config('services.payme.merchant_id');
        $paymeUrl = null;
        if ($paymeMerchantId) {
            $amountInTiyin = $order->grand_total * 100;
            $paymeParams = "m={$paymeMerchantId};ac.order_id={$order->id};a={$amountInTiyin}";
            $paymeUrl = "https://checkout.paycom.uz/" . base64_encode($paymeParams);
        }

        return view('storefront.payment_checkout', compact(
            'order',
            'payment',
            'clickUrl',
            'paymeUrl'
        ));
    }

    /**
     * Process payment action: instant test settlement, Cash on Delivery confirmation, or gateway redirect.
     */
    public function pay(Request $request, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:cash_on_delivery,click,payme,uzum,demo',
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $method = $validated['payment_method'];

        // 1. Handle Cash on Delivery
        if ($method === 'cash_on_delivery') {
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'amount' => $order->grand_total,
                    'currency' => $order->currency,
                ]
            );

            $payment->update([
                'payment_method' => 'cash_on_delivery',
                'status' => 'pending',
                'transaction_id' => $payment->transaction_id ?? ('COD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)))),
            ]);

            // Confirm order and lock stock for delivery
            $order->update(['status' => 'processing']);
            foreach ($order->suborders as $suborder) {
                $suborder->update(['status' => 'confirmed']);
            }

            InventoryReservation::where('order_id', $order->id)
                ->where('status', 'reserved')
                ->update(['status' => 'committed']);

            return redirect()->route('order.success', ['orderNumber' => $order->order_number])
                ->with('success', "To'lov usuli: Yetkazib berilganda to'lash (Naqd yoki Karta) tanlandi! Buyurtmangiz tasdiqlandi va yetkazishga tayyorlanmoqda.");
        }

        // 2. Handle Online Payments (Click / Payme / Uzum)
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'amount' => $order->grand_total,
                'currency' => $order->currency,
                'transaction_id' => 'TXN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                'status' => 'pending',
            ]
        );

        $payment->update([
            'payment_method' => $method,
        ]);

        // If real production Click is configured
        if ($method === 'click' && config('services.click.service_id') && config('services.click.merchant_id')) {
            $serviceId = config('services.click.service_id');
            $merchantId = config('services.click.merchant_id');
            $returnUrl = route('order.success', ['orderNumber' => $order->order_number]);
            return redirect()->away("https://my.click.uz/services/pay?service_id={$serviceId}&merchant_id={$merchantId}&amount={$order->grand_total}&transaction_param={$order->id}&return_url=" . urlencode($returnUrl));
        }

        // If real production Payme is configured
        if ($method === 'payme' && config('services.payme.merchant_id')) {
            $merchantId = config('services.payme.merchant_id');
            $amountInTiyin = $order->grand_total * 100;
            $paymeParams = "m={$merchantId};ac.order_id={$order->id};a={$amountInTiyin}";
            return redirect()->away("https://checkout.paycom.uz/" . base64_encode($paymeParams));
        }

        // 3. Instant Safe Settlement (Sandbox / Simulation with Cryptographic HMAC verification)
        try {
            $eventId = 'EVT-WEB-' . strtoupper(bin2hex(random_bytes(6)));
            $secretKey = config('services.payment.demo_secret', 'marketplace-sandbox-hmac-secret-key-2026');
            $signature = hash_hmac('sha256', "{$eventId}:{$payment->transaction_id}:{$order->id}:{$payment->amount}", $secretKey);

            $this->paymentAdapter->processWebhook([
                'event_id' => $eventId,
                'transaction_id' => $payment->transaction_id,
                'order_id' => $order->id,
                'amount' => $payment->amount,
                'provider' => $method,
            ], $signature);

            $methodName = match($method) {
                'click' => 'Click Up',
                'payme' => 'Payme',
                'uzum' => 'Uzum Bank',
                default => 'Onlayn To\'lov',
            };

            return redirect()->route('order.success', ['orderNumber' => $order->order_number])
                ->with('success', "{$methodName} orqali to'lov muvaffaqiyatli amalga oshirildi! Chek raqami: #{$payment->transaction_id}");
        } catch (\Throwable $e) {
            return back()->with('error', "To'lovni tasdiqlashda xatolik yuz berdi: " . $e->getMessage());
        }
    }
}
