<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentWebController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager
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

        $isClickConfigured = $this->paymentManager->isProviderConfigured('click');
        $isPaymeConfigured = $this->paymentManager->isProviderConfigured('payme');
        $isDemoAllowed = $this->paymentManager->isProviderConfigured('demo');

        // Generate Click URL if configured
        $clickUrl = null;
        if ($isClickConfigured) {
            try {
                $session = $this->paymentManager->click()->initializePayment($order);
                $clickUrl = $session['payment_url'] ?? null;
            } catch (\Throwable) {
                $clickUrl = null;
            }
        }

        // Generate Payme URL if configured
        $paymeUrl = null;
        if ($isPaymeConfigured) {
            try {
                $session = $this->paymentManager->payme()->initializePayment($order);
                $paymeUrl = $session['payment_url'] ?? null;
            } catch (\Throwable) {
                $paymeUrl = null;
            }
        }

        return view('storefront.payment_checkout', compact(
            'order',
            'payment',
            'clickUrl',
            'paymeUrl',
            'isClickConfigured',
            'isPaymeConfigured',
            'isDemoAllowed'
        ));
    }

    /**
     * Process payment action: gateway redirect, Cash on Delivery confirmation, or sandbox simulation.
     */
    public function pay(Request $request, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:cash_on_delivery,click,payme,uzum,demo',
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $method = $validated['payment_method'];

        // 1. Handle Cash on Delivery (Payment remains pending until courier marks delivered)
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

        // 2. Real Production Click Redirect
        if ($method === 'click') {
            if (!$this->paymentManager->isProviderConfigured('click')) {
                return back()->with('error', "Click to'lov tizimi hozircha sozlanmagan (Merchant rekvizitlari kiritilmagan). Iltimos, boshqa to'lov usulini tanlang yoki do'kon ma'muriyati bilan bog'laning.");
            }

            try {
                $session = $this->paymentManager->click()->initializePayment($order);
                return redirect()->away($session['payment_url']);
            } catch (\Throwable $e) {
                return back()->with('error', "Click to'lov tizimiga ulanishda xatolik: " . $e->getMessage());
            }
        }

        // 3. Real Production Payme Redirect
        if ($method === 'payme') {
            if (!$this->paymentManager->isProviderConfigured('payme')) {
                return back()->with('error', "Payme to'lov tizimi hozircha sozlanmagan (Merchant rekvizitlari kiritilmagan). Iltimos, boshqa to'lov usulini tanlang yoki do'kon ma'muriyati bilan bog'laning.");
            }

            try {
                $session = $this->paymentManager->payme()->initializePayment($order);
                return redirect()->away($session['payment_url']);
            } catch (\Throwable $e) {
                return back()->with('error', "Payme to'lov tizimiga ulanishda xatolik: " . $e->getMessage());
            }
        }

        // 4. Demo Sandbox Settlement (Strictly allowed ONLY in local/dev test mode when explicitly enabled)
        if ($method === 'demo') {
            if (!$this->paymentManager->isProviderConfigured('demo')) {
                return back()->with('error', "Demo sinov to'lovi faol emas yoki ishlab chiqarish (production) tizimida taqiqlangan!");
            }

            try {
                $payment = Payment::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'amount' => $order->grand_total,
                        'currency' => $order->currency,
                        'transaction_id' => 'TXN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                        'status' => 'pending',
                    ]
                );

                $eventId = 'EVT-WEB-' . strtoupper(bin2hex(random_bytes(6)));
                $secretKey = config('services.payment.demo_secret', 'marketplace-sandbox-hmac-secret-key-2026');
                $signature = hash_hmac('sha256', "{$eventId}:{$payment->transaction_id}:{$order->id}:{$payment->amount}", $secretKey);

                $this->paymentManager->demo()->processWebhook([
                    'event_id' => $eventId,
                    'transaction_id' => $payment->transaction_id,
                    'order_id' => $order->id,
                    'amount' => $payment->amount,
                    'provider' => 'demo_sandbox',
                ], $signature);

                return redirect()->route('order.success', ['orderNumber' => $order->order_number])
                    ->with('success', "Sandbox Demo to'lovi muvaffaqiyatli yakunlandi! Chek raqami: #{$payment->transaction_id}");
            } catch (\Throwable $e) {
                return back()->with('error', "Demo to'lovda xatolik: " . $e->getMessage());
            }
        }

        // 5. Fallback for unintegrated providers
        return back()->with('error', "Tanlangan to'lov usuli hozirda faol emas. Iltimos, Click, Payme yoki Kuryerga to'lash usulini tanlang.");
    }
}
