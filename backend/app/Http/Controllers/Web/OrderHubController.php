<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Cart\CartService;
use App\Services\Checkout\InventoryReservationService;
use App\Services\Order\OrderCancellationService;
use App\Services\Refund\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderHubController extends Controller
{
    /** Days after delivery during which a customer may ask to return goods. */
    public const RETURN_WINDOW_DAYS = 10;

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

        $isOwner = $order->user_id === $user->id;
        $canCancel = $isOwner && $this->isCancellable($order);

        $order->load(['refunds', 'payments']);
        $returnableSuborderIds = $isOwner
            ? $order->suborders->filter(fn($sub) => $this->isReturnable($order, $sub))->pluck('id')->all()
            : [];

        return view('storefront.order_detail', compact('order', 'canCancel', 'isOwner', 'returnableSuborderIds'));
    }

    /**
     * "Buy again": put every item of a past order back into the cart at today's prices.
     * Items that are no longer sold are skipped and reported.
     */
    public function reorder(Request $request, string $orderNumber, CartService $cartService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with('suborders.items.variant.product')
            ->firstOrFail();

        $sessionId = (string) ($request->cookie('cart_session_id') ?: $request->session()->getId());
        $cart = $cartService->getCartForUserOrSession($user, $sessionId);

        $added = 0;
        $skipped = [];
        foreach ($order->suborders->flatMap->items as $item) {
            $product = $item->variant?->product;
            if (!$item->variant?->is_active || !$product || $product->status !== 'approved' || !$product->is_active) {
                $skipped[] = $item->product_name;
                continue;
            }

            try {
                $cartService->addItem($cart, $item->product_variant_id, (float) $item->quantity);
                $added++;
            } catch (\Throwable) {
                $skipped[] = $item->product_name;
            }
        }

        if ($added === 0) {
            return back()->with('error', "Bu buyurtmadagi mahsulotlar hozir sotuvda yo'q.");
        }

        $message = "{$added} ta mahsulot savatchaga qo'shildi. Narxlar bugungi narxda hisoblanadi.";
        if ($skipped) {
            $message .= ' Sotuvda yo\'q: ' . implode(', ', array_unique($skipped)) . '.';
        }

        return redirect()->route('cart')->with('success', $message);
    }

    /**
     * Customer asks to return a delivered part of their order (one seller's parcel).
     */
    public function requestReturn(Request $request, string $orderNumber, RefundService $refundService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with(['suborders.shipments', 'payments', 'refunds'])
            ->firstOrFail();

        $validated = $request->validate([
            'suborder_id' => 'required|integer',
            'reason' => 'required|string|max:100',
            'details' => 'nullable|string|max:400',
        ]);

        $suborder = $order->suborders->firstWhere('id', (int) $validated['suborder_id']);
        abort_unless($suborder, 404);

        if (!$this->isReturnable($order, $suborder)) {
            return back()->with('error', "Bu qism uchun qaytarish so'rovini yuborib bo'lmaydi.");
        }

        $reason = $validated['reason'] . (!empty($validated['details']) ? ': ' . $validated['details'] : '');

        try {
            $refund = $refundService->requestRefund($user, $order, $suborder->refundableGoodsAmount(), $reason, $suborder);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.detail', $order->order_number)
            ->with('success', "Qaytarish so'rovi #{$refund->refund_number} yuborildi. Moderator ko'rib chiqib, siz bilan bog'lanadi.");
    }

    /**
     * Returnable: this part was delivered within the last RETURN_WINDOW_DAYS days, the order is paid,
     * and there is no open or finished return for it yet.
     */
    protected function isReturnable(Order $order, Suborder $suborder): bool
    {
        if ($suborder->status !== 'delivered' || !$order->payments->contains('status', 'paid')) {
            return false;
        }

        $deliveredAt = $suborder->shipments->max('delivered_at') ?? $suborder->updated_at;
        if ($deliveredAt && $deliveredAt->lt(now()->subDays(self::RETURN_WINDOW_DAYS))) {
            return false;
        }

        return !$order->refunds->where('suborder_id', $suborder->id)->where('status', '!=', 'rejected')->count();
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

        app(OrderCancellationService::class)->cancel(
            $order,
            $user,
            'order.cancelled_by_customer',
            $validated['reason'] ?? null,
            'Mijoz buyurtmani bekor qildi'
        );

        return redirect()->route('orders.detail', $order->order_number)
            ->with('success', "Buyurtma bekor qilindi.");
    }

    /**
     * Cancellable while unpaid online or cash-on-delivery, and no parcel has been picked up yet.
     */
    protected function isCancellable(Order $order): bool
    {
        return app(OrderCancellationService::class)->isCancellable($order);
    }
}
