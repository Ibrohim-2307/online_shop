<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Checkout\InventoryReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderHubController extends Controller
{
    /**
     * Unified Orders & Deliveries Hub (Buyurtmalar va Yetkazib Berishlar Markazi).
     * Displays:
     * 1. Purchases (Sotib olingan tovarlar / Yetkazib berilayotgan xaridlar)
     * 2. Sales Orders (Do'kondan sotib olishga berilgan buyurtmalar - for sellers)
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // If not logged in, redirect to login page
        if (!$user) {
            return redirect()->guest(route('login'))->with('error', "Buyurtmalaringizni ko'rish uchun profilingizga kiring.");
        }

        $activeTab = $request->query('tab', 'purchases'); // 'purchases' or 'sales'
        $statusFilter = $request->query('status', 'all');

        // 1. Inbound Purchases (Sotib olgan narsalari) - Orders placed by this user
        $purchasesQuery = Order::where('user_id', $user->id)
            ->with([
                'suborders.seller',
                'suborders.items',
                'suborders.shipments.courier',
            ])
            ->latest();

        if ($statusFilter !== 'all') {
            if ($statusFilter === 'in_transit') {
                $purchasesQuery->whereHas('suborders.shipments', fn($q) => $q->where('status', 'in_transit'));
            } else {
                $purchasesQuery->where('status', $statusFilter);
            }
        }

        $purchases = $purchasesQuery->get();

        // 2. Outbound Sales (Mijozlar bergan buyurtmalar / Sotuvlar) - Suborders belonging to user's seller store
        $isSeller = false;
        $sales = collect();
        $seller = null;

        if ($user->hasRole(['seller_owner', 'seller_manager', 'seller_operator', 'admin'])) {
            $isSeller = true;
            // Never fall back to another store: a user without a store simply has no sales
            $sellerId = $user->seller_id;
            $seller = $sellerId ? Seller::find($sellerId) : null;

            if ($seller) {
                $salesQuery = Suborder::where('seller_id', $seller->id)
                    ->with([
                        'order.user',
                        'items',
                        'shipments.courier',
                    ])
                    ->latest();

                if ($statusFilter !== 'all') {
                    if ($statusFilter === 'in_transit') {
                        $salesQuery->whereHas('shipments', fn($q) => $q->where('status', 'in_transit'));
                    } else {
                        $salesQuery->where('status', $statusFilter);
                    }
                }

                $sales = $salesQuery->get();
            }
        }

        // Metrics for summary badges
        $totalPurchasedSum = $purchases->sum('grand_total');
        $activeInboundDeliveries = $purchases->filter(fn($o) => 
            $o->suborders->flatMap->shipments->contains(fn($s) => $s->status === 'in_transit')
        )->count();

        $totalSalesSum = $sales->sum('total_amount');
        $activeOutboundDeliveries = $sales->filter(fn($s) => 
            $s->shipments->contains(fn($sh) => $sh->status === 'in_transit')
        )->count();

        return view('storefront.orders_hub', compact(
            'user',
            'isSeller',
            'seller',
            'activeTab',
            'statusFilter',
            'purchases',
            'sales',
            'totalPurchasedSum',
            'activeInboundDeliveries',
            'totalSalesSum',
            'activeOutboundDeliveries'
        ));
    }

    /**
     * Show detailed single order view.
     */
    public function show(Request $request, string $orderNumber): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->guest(route('login'))->with('error', "Buyurtma tafsilotlarini ko'rish uchun profilingizga kiring.");
        }

        $order = Order::where('order_number', $orderNumber)
            ->with([
                'user',
                'suborders.seller',
                'suborders.items',
                'suborders.shipments.courier',
            ])
            ->firstOrFail();

        // Security check: buyer, staff, the assigned courier, or a seller fulfilling part of this order
        if (!StorefrontController::canAccessOrder($request, $order)) {
            abort(403, "Ushbu buyurtmani ko'rishga ruxsat berilmagan.");
        }

        $canCancel = $order->user_id === $user->id && $this->isCancellable($order);

        return view('storefront.order_detail', compact('order', 'canCancel'));
    }

    /**
     * Customer cancels their own order before it leaves the warehouse.
     * Reserved stock is returned; orders already paid online go through the refund flow instead.
     */
    public function cancel(Request $request, string $orderNumber, InventoryReservationService $reservations): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with(['suborders.shipments', 'payments', 'reservations'])
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        if (!$this->isCancellable($order)) {
            return back()->with('error', "Bu buyurtmani endi bekor qilib bo'lmaydi. Agar mahsulot yetib kelgan bo'lsa, qaytarish so'rovini yuboring.");
        }

        DB::transaction(function () use ($order, $reservations, $validated, $user) {
            $previousStatus = $order->status;

            foreach ($order->reservations as $reservation) {
                $reservations->releaseReservation($reservation, 'Customer cancelled order ' . $order->order_number, true);
            }

            $order->update(['status' => 'cancelled']);
            foreach ($order->suborders as $suborder) {
                $suborder->update(['status' => 'cancelled']);
                foreach ($suborder->shipments as $shipment) {
                    $shipment->update(['status' => 'failed', 'delay_reason' => 'Mijoz buyurtmani bekor qildi']);
                }
            }
            $order->payments()->where('status', 'pending')->update(['status' => 'cancelled']);

            AuditService::log(
                'order.cancelled_by_customer',
                $order,
                ['status' => $previousStatus],
                ['status' => 'cancelled', 'reason' => $validated['reason'] ?? null],
                $user
            );
        });

        return redirect()->route('orders.detail', $order->order_number)
            ->with('success', "Buyurtma bekor qilindi.");
    }

    /**
     * Cancellable while unpaid online or cash-on-delivery, and no parcel has been picked up yet.
     */
    protected function isCancellable(Order $order): bool
    {
        if (!in_array($order->status, ['pending_payment', 'processing'], true)) {
            return false;
        }

        if ($order->payments->contains('status', 'paid')) {
            return false;
        }

        return $order->suborders->flatMap->shipments
            ->every(fn($shipment) => in_array($shipment->status, ['pending', 'assigned'], true));
    }
}
