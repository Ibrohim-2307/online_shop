<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\SellerPayout;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the Superadmin / Admin Dashboard web page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin', 'moderator'])) {
            return redirect()->route('login')->with('error', 'Superadmin paneliga kirish uchun administrator hisobi talab qilinadi.');
        }

        $couriers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['courier', 'logistics_operator']))->get();

        $stats = [
            'total_users' => User::count(),
            'total_sellers' => Seller::count(),
            'active_sellers' => Seller::where('status', 'active')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::whereIn('status', ['paid', 'processing', 'completed'])->sum('grand_total'),
            'total_couriers' => $couriers->count(),
            'active_shipments' => Shipment::whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])->count(),
        ];

        $sellers = Seller::with('members.user')->latest()->get();
        $users = User::with('roles')->latest()->paginate(15);
        $roles = Role::orderBy('id')->get();
        $products = Product::with(['category', 'seller', 'variants.priceTiers', 'variants.inventoryBalances.warehouse'])->latest()->paginate(15);
        $recentOrders = Order::with(['suborders.seller', 'suborders.shipments'])->latest()->limit(15)->get();
        $shipments = Shipment::with(['courier', 'suborder.seller', 'suborder.order.user'])->latest()->paginate(15);
        $auditLogs = AuditLog::with('user')->latest()->limit(20)->get();
        $payouts = SellerPayout::with('seller')->latest()->limit(10)->get();

        return view('admin.dashboard', compact(
            'stats',
            'sellers',
            'users',
            'roles',
            'products',
            'recentOrders',
            'shipments',
            'couriers',
            'auditLogs',
            'payouts'
        ));
    }

    /**
     * Update product moderation status (approve / reject / toggle active).
     */
    public function updateProductStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin', 'moderator'])) {
            abort(403, 'Faqat moderator yoki administrator tovar holatini o\'zgartirishi mumkin.');
        }

        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product = Product::findOrFail($id);

        if (isset($validated['status'])) {
            $product->status = $validated['status'];
        }
        if (isset($validated['is_active'])) {
            $product->is_active = (bool) $validated['is_active'];
        }
        $product->save();

        AuditService::log('admin.product_status_updated', $product, null, [
            'status' => $product->status,
            'is_active' => $product->is_active,
        ]);

        return back()->with('success', "Mahsulot ({$product->name}) holati muvaffaqiyatli yangilandi.");
    }

    /**
     * Update seller status (approve / suspend / activate).
     */
    public function updateSellerStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Faqat administrator sotuvchi holatini o\'zgartirishi mumkin.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,suspended'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
        ]);

        $seller = Seller::findOrFail($id);
        $seller->status = $validated['status'];
        if (isset($validated['commission_rate'])) {
            $seller->commission_rate = (float) $validated['commission_rate'];
        }
        $seller->save();

        AuditService::log('admin.seller_status_updated', $seller, null, [
            'status' => $seller->status,
            'commission_rate' => $seller->commission_rate,
        ]);

        return back()->with('success', "Do'kon ({$seller->name}) holati muvaffaqiyatli '{$validated['status']}' ga o'zgartirildi.");
    }

    /**
     * Update user active status (block / unblock).
     */
    public function updateUserStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Faqat administrator foydalanuvchini bloklashi mumkin.');
        }

        $targetUser = User::findOrFail($id);
        if ($targetUser->hasRole(['superadmin', 'admin']) && $targetUser->id === $user->id) {
            return back()->with('error', 'O\'z hisobingizni bloklay olmaysiz.');
        }

        $targetUser->update(['is_active' => !$targetUser->is_active]);

        AuditService::log('admin.user_status_toggled', $targetUser, null, [
            'is_active' => $targetUser->is_active,
        ]);

        $statusText = $targetUser->is_active ? 'faollashtirildi' : 'bloklandi';
        return back()->with('success', "Foydalanuvchi ({$targetUser->name}) hisobi {$statusText}.");
    }

    /**
     * Superadmin: Update or change any user's role (promote to Seller, Courier, Admin, Customer).
     */
    public function updateUserRole(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Faqat superadmin foydalanuvchi rolini o\'zgartira oladi.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $targetUser = User::findOrFail($id);
        $roleName = $validated['role'];
        $role = Role::where('name', $roleName)->firstOrFail();

        // Sync role (single primary role or promote)
        $targetUser->roles()->sync([$role->id]);

        // If promoted to seller, ensure seller store exists
        if (in_array($roleName, ['seller_owner', 'seller_manager', 'seller_operator'])) {
            $seller = Seller::where('id', $targetUser->seller_id)->first();
            if (!$seller) {
                $seller = Seller::firstOrCreate(
                    ['slug' => Str::slug($targetUser->name . '-store')],
                    [
                        'name' => $targetUser->name . " Do'koni",
                        'legal_name' => $targetUser->name . " XK",
                        'status' => 'active',
                        'commission_rate' => 5.0,
                        'rating' => 5.0,
                        'phone' => $targetUser->phone,
                        'email' => $targetUser->email,
                    ]
                );
            }
            SellerMember::firstOrCreate(
                ['seller_id' => $seller->id, 'user_id' => $targetUser->id],
                ['role' => 'owner', 'is_active' => true]
            );
            $targetUser->update(['seller_id' => $seller->id]);
        }

        AuditService::log('admin.user_role_changed', $targetUser, null, [
            'new_role' => $roleName,
            'assigned_by' => $user->id,
        ]);

        return back()->with('success', "Foydalanuvchi ({$targetUser->name}) roli '{$role->label}' ga muvaffaqiyatli o'zgartirildi.");
    }

    /**
     * Superadmin: Assign courier to a shipment.
     */
    public function assignCourier(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin', 'moderator'])) {
            abort(403, 'Faqat administrator kuryer biriktira oladi.');
        }

        $validated = $request->validate([
            'courier_id' => ['required', 'exists:users,id'],
        ]);

        $shipment = Shipment::findOrFail($id);
        $courier = User::findOrFail($validated['courier_id']);

        $shipment->update([
            'courier_id' => $courier->id,
            'status' => $shipment->status === 'pending' ? 'assigned' : $shipment->status,
        ]);

        AuditService::log('admin.shipment_courier_assigned', $shipment, null, [
            'courier_id' => $courier->id,
            'courier_name' => $courier->name,
        ]);

        return back()->with('success', "Kuryer ({$courier->name}) jo'natma #{$shipment->tracking_number} ga biriktirildi.");
    }
}
