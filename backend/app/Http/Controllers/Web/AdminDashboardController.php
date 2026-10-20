<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerPayout;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Dashboard web page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['admin', 'moderator'])) {
            return redirect()->route('login')->with('error', 'Admin panelga kirish uchun administrator hisobi talab qilinadi.');
        }

        $stats = [
            'total_users' => User::count(),
            'total_sellers' => Seller::count(),
            'active_sellers' => Seller::where('status', 'active')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'paid')->sum('grand_total'),
        ];

        $sellers = Seller::with('members.user')->latest()->get();
        $users = User::with('roles')->latest()->paginate(15);
        $products = Product::with(['category', 'seller', 'variants.priceTiers', 'variants.inventoryBalances.warehouse'])->latest()->paginate(15);
        $recentOrders = Order::with(['suborders.seller', 'suborders.shipments'])->latest()->limit(10)->get();
        $auditLogs = AuditLog::with('user')->latest()->limit(15)->get();
        $payouts = SellerPayout::with('seller')->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'sellers', 'users', 'products', 'recentOrders', 'auditLogs', 'payouts'));
    }

    /**
     * Update product moderation status (approve / reject / toggle active).
     */
    public function updateProductStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['admin', 'moderator'])) {
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
            $product->is_active = $validated['is_active'];
        }
        $product->save();

        return back()->with('success', "Mahsulot ({$product->name}) holati muvaffaqiyatli yangilandi.");
    }

    /**
     * Update seller status (approve / suspend / activate).
     */
    public function updateSellerStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Faqat administrator sotuvchi holatini o\'zgartirishi mumkin.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,suspended'],
        ]);

        $seller = Seller::findOrFail($id);
        $seller->update(['status' => $validated['status']]);

        return back()->with('success', "Do'kon ({$seller->name}) holati muvaffaqiyatli '{$validated['status']}' ga o'zgartirildi.");
    }

    /**
     * Update user active status.
     */
    public function updateUserStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Faqat administrator foydalanuvchini bloklashi mumkin.');
        }

        $targetUser = User::findOrFail($id);
        if ($targetUser->hasRole('admin') && $targetUser->id === $user->id) {
            return back()->with('error', 'O\'z hisobingizni bloklay olmaysiz.');
        }

        $targetUser->update(['is_active' => !$targetUser->is_active]);

        $statusText = $targetUser->is_active ? 'faollashtirildi' : 'bloklandi';
        return back()->with('success', "Foydalanuvchi ({$targetUser->name}) hisobi {$statusText}.");
    }
}
