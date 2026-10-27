<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $sellerId = $user->seller_id;
            $seller = $sellerId ? Seller::find($sellerId) : Seller::first();

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
    public function show(string $orderNumber): View|\Illuminate\Http\RedirectResponse
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

        // Security check: Customer can only view their own orders unless they have staff/admin/seller/courier roles
        if (!$user->hasRole(['admin', 'superadmin', 'moderator', 'seller_owner', 'seller_manager', 'courier', 'logistics_operator']) && $order->user_id !== $user->id) {
            abort(403, "Ushbu buyurtmani ko'rishga ruxsat berilmagan.");
        }

        return view('storefront.order_detail', compact('order'));
    }
}
