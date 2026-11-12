<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\Refund;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\SellerPayout;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Backup\DatabaseBackupService;
use App\Services\Ledger\SellerLedgerService;
use App\Services\Refund\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            // Only money that actually came in: "processing" also holds unpaid cash-on-delivery and nasiya orders
            'total_revenue' => (int) \App\Models\Payment::whereIn('status', ['paid', 'partially_refunded'])->sum('amount')
                - (int) Refund::where('status', 'completed')
                    ->whereIn('order_id', \App\Models\Payment::where('status', 'partially_refunded')->select('order_id'))
                    ->sum('amount'),
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
        // Paying sellers moves real money, so moderators only see that it exists
        $canManagePayouts = $user->hasRole(['superadmin', 'admin']);
        $payouts = $canManagePayouts
            ? SellerPayout::with('seller')
                ->orderByRaw("CASE WHEN status IN ('requested', 'held', 'processing') THEN 0 ELSE 1 END")
                ->latest('id')
                ->limit(50)
                ->get()
            : collect();
        $stats['open_payouts'] = $canManagePayouts ? $payouts->filter->isOpen()->count() : 0;
        $stats['open_payouts_sum'] = $canManagePayouts ? (int) $payouts->filter->isOpen()->sum('amount') : 0;
        $ledgerService = app(SellerLedgerService::class);
        $payoutBalances = $payouts->filter->isOpen()->pluck('seller_id')->unique()
            ->mapWithKeys(fn($sellerId) => [$sellerId => $ledgerService->getSellerBalance($sellerId)]);

        // Open return requests first, then the most recent decisions
        $refunds = Refund::with(['order', 'suborder.seller', 'suborder.items', 'user', 'moderator'])
            ->orderByRaw("CASE status WHEN 'requested' THEN 0 WHEN 'approved' THEN 1 WHEN 'goods_returned' THEN 1 ELSE 2 END")
            ->latest()
            ->limit(50)
            ->get();
        $stats['open_refunds'] = Refund::whereIn('status', ['requested', 'approved', 'goods_returned'])->count();

        // A database copy holds every customer's data, so moderators get no access to it
        $canManageBackups = $user->hasRole(['superadmin', 'admin']);
        $backups = $canManageBackups ? app(DatabaseBackupService::class)->all() : collect();
        $lastDailyBackup = $backups->firstWhere('type', 'daily');

        // Categories & Marketing
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        $promocodes = Promocode::latest()->get();
        $banners = Banner::orderBy('sort_order')->latest()->get();

        // 14 days daily sales analytics for charts
        $dailySales = Order::where('created_at', '>=', now()->subDays(14))
            ->whereIn('status', ['processing', 'completed'])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count, SUM(grand_total) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 5 selling products by revenue (cancelled orders are not sales)
        $topProducts = OrderItem::whereHas('suborder', fn($q) => $q->where('status', '!=', 'cancelled')
                ->whereHas('order', fn($o) => $o->whereNotIn('status', ['cancelled', 'pending_payment'])))
            ->selectRaw("product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue")
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Top 5 sellers by revenue
        $topSellers = Suborder::with('seller')
            ->where('status', '!=', 'cancelled')
            ->whereHas('order', fn($o) => $o->whereNotIn('status', ['cancelled', 'pending_payment']))
            ->selectRaw("seller_id, COUNT(*) as count, SUM(total_amount) as total_revenue")
            ->groupBy('seller_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Blocked / Suspicious Users
        $blockedUsers = User::where('is_active', false)->with('roles')->latest()->get();

        $tabs = ['sellers', 'products', 'categories', 'marketing', 'analytics', 'users', 'shipments', 'orders', 'refunds', 'audit'];
        if ($canManageBackups) {
            $tabs[] = 'backups';
        }
        if ($canManagePayouts) {
            $tabs[] = 'payouts';
            $tabs[] = 'finance';
        }
        $finance = $canManagePayouts ? $ledgerService->platformSummary() : null;
        $initialTab = in_array($request->query('tab'), $tabs, true)
            ? $request->query('tab')
            : 'sellers';

        return view('admin.dashboard', compact(
            'canManageBackups',
            'backups',
            'lastDailyBackup',
            'refunds',
            'initialTab',
            'stats',
            'sellers',
            'users',
            'blockedUsers',
            'roles',
            'products',
            'categories',
            'promocodes',
            'banners',
            'dailySales',
            'topProducts',
            'topSellers',
            'recentOrders',
            'shipments',
            'couriers',
            'auditLogs',
            'payouts',
            'payoutBalances',
            'finance',
            'canManagePayouts'
        ));
    }

    /**
     * Administrator has transferred the money (or refuses to): close the seller's payout request.
     */
    public function reviewPayout(Request $request, int $id, SellerLedgerService $ledgerService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, "Sotuvchiga pul to'lashni faqat administrator tasdiqlay oladi.");
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:paid,reject'],
            'notes' => ['nullable', 'string', 'max:500', 'required_if:decision,reject'],
        ], [
            'notes.required_if' => "Rad etish sababini yozing — u sotuvchiga ko'rsatiladi.",
        ]);

        $payout = SellerPayout::with('seller')->findOrFail($id);
        $back = redirect()->route('admin.dashboard', ['tab' => 'payouts']);

        try {
            $ledgerService->reviewPayout(
                $payout,
                $validated['decision'] === 'paid' ? 'completed' : 'rejected',
                $validated['notes'] ?? null,
                $user
            );
        } catch (\InvalidArgumentException $e) {
            return $back->with('error', $e->getMessage());
        }

        $sum = number_format($payout->amount, 0, '.', ' ');
        $store = $payout->seller?->name ?? "Do'kon";

        return $back->with('success', $validated['decision'] === 'paid'
            ? "{$store}: {$sum} UZS to'langan deb belgilandi va do'kon balansidan ayirildi."
            : "{$store}: {$sum} UZS so'rovi rad etildi, pul do'kon balansida qoldi.");
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
        if ($targetUser->id === $user->id) {
            return back()->with('error', 'O\'z hisobingizni bloklay olmaysiz.');
        }

        // Security check: A standard admin cannot block a superadmin
        if ($targetUser->hasRole('superadmin') && !$user->hasRole('superadmin')) {
            return back()->with('error', "Standart administrator Superadmin hisobini bloklay olmaydi.");
        }

        // Security check: Prevent blocking the last active superadmin
        if ($targetUser->hasRole('superadmin') && $targetUser->is_active) {
            $activeSuperadmins = User::whereHas('roles', fn($q) => $q->where('name', 'superadmin'))->where('is_active', true)->count();
            if ($activeSuperadmins <= 1) {
                return back()->with('error', "Tizimdagi yagona faol Superadministratorni bloklab bo'lmaydi.");
            }
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

        // Security check: Only superadmin can assign admin or superadmin roles
        if (in_array($roleName, ['superadmin', 'admin'], true) && !$user->hasRole('superadmin')) {
            return back()->with('error', "Faqat Superadministrator boshqa foydalanuvchilarga administrator yoki superadmin rolini bera oladi.");
        }

        // Security check: A standard admin cannot demote or modify an existing superadmin
        if ($targetUser->hasRole('superadmin') && !$user->hasRole('superadmin')) {
            return back()->with('error', "Standart administrator Superadmin hisobini o'zgartira olmaydi.");
        }

        // Security check: Prevent removing the last active superadmin
        if ($targetUser->hasRole('superadmin') && $roleName !== 'superadmin') {
            $superadminCount = User::whereHas('roles', fn($q) => $q->where('name', 'superadmin'))->where('is_active', true)->count();
            if ($superadminCount <= 1) {
                return back()->with('error', "Tizimdagi yagona Superadministrator rolini olib tashlab bo'lmaydi.");
            }
        }

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
     * Superadmin / Admin: Reset any user's password (superadmin, admin, seller, customer, courier).
     */
    public function updateUserPassword(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, "Faqat administrator foydalanuvchi parolini o'zgartira oladi.");
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $targetUser = User::findOrFail($id);

        // Security check: Only superadmin can reset administrative or moderator accounts
        if ($targetUser->hasRole(['superadmin', 'admin', 'moderator']) && !$user->hasRole('superadmin')) {
            return back()->with('error', "Administrator yoki moderator parolini faqat Bosh Superadmin o'zgartira oladi.");
        }

        $targetUser->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        // Revoke all existing Sanctum personal access tokens
        $targetUser->tokens()->delete();

        // Invalidate active web sessions from database if sessions table exists
        if (Schema::hasTable('sessions')) {
            $currentSessionId = session()->getId();
            if ($user->id === $targetUser->id && $currentSessionId) {
                DB::table('sessions')
                    ->where('user_id', $targetUser->id)
                    ->where('id', '!=', $currentSessionId)
                    ->delete();
            } else {
                DB::table('sessions')->where('user_id', $targetUser->id)->delete();
            }
        }

        AuditService::log('admin.user_password_reset', $targetUser, null, [
            'reset_by' => $user->id,
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
        ]);

        return back()->with('success', "Foydalanuvchi ({$targetUser->name}) paroli muvaffaqiyatli yangilandi va barcha faol sessiyalari tugatildi.");
    }

    /**
     * Moderator approves or rejects a customer's return request.
     */
    public function reviewRefund(Request $request, int $id, RefundService $refundService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin', 'moderator'])) {
            abort(403, "Faqat moderator yoki administrator qaytarish so'rovini ko'rib chiqa oladi.");
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'notes' => ['nullable', 'string', 'max:500', 'required_if:decision,reject'],
        ], [
            'notes.required_if' => "Rad etish sababini yozing — u mijozga ko'rsatiladi.",
        ]);

        $refund = Refund::findOrFail($id);

        try {
            $refundService->reviewRefund($refund, $user, $validated['decision'] === 'approve', $validated['notes'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'refunds'])->with('error', $e->getMessage());
        }

        $message = $validated['decision'] === 'approve'
            ? "So'rov #{$refund->refund_number} tasdiqlandi. Tovar qaytib kelgach \"Pul qaytarildi\" ni bosing."
            : "So'rov #{$refund->refund_number} rad etildi.";

        return redirect()->route('admin.dashboard', ['tab' => 'refunds'])->with('success', $message);
    }

    /**
     * Goods are back and the customer has been paid back: restock, debit the seller, close the request.
     */
    public function completeRefund(int $id, RefundService $refundService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin', 'moderator'])) {
            abort(403, "Faqat moderator yoki administrator qaytarishni yakunlay oladi.");
        }

        $refund = Refund::findOrFail($id);

        try {
            $result = $refundService->completeRefundWithGoodsRestock($refund, $user);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'refunds'])->with('error', $e->getMessage());
        }

        $message = $result['status'] === 'already_completed'
            ? "So'rov #{$refund->refund_number} avval yakunlangan."
            : "Qaytarish #{$refund->refund_number} yakunlandi: tovar omborga qaytdi, summa sotuvchi hisobidan ayirildi.";

        return redirect()->route('admin.dashboard', ['tab' => 'refunds'])->with('success', $message);
    }

    /**
     * Take a database copy right now, e.g. before a risky change.
     */
    public function createBackup(DatabaseBackupService $backups): RedirectResponse
    {
        $this->authorizeBackups();

        try {
            $backup = $backups->create('manual');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.dashboard', ['tab' => 'backups'])
                ->with('error', "Zaxira nusxa olinmadi. Serverdagi xatolar jurnalini tekshiring.");
        }

        AuditService::log('admin.backup_created', null, null, ['file' => $backup['name']]);

        return redirect()->route('admin.dashboard', ['tab' => 'backups'])
            ->with('success', "Zaxira nusxa olindi: {$backup['name']}");
    }

    /**
     * Download a database copy so it can be kept away from the server.
     */
    public function downloadBackup(string $filename, DatabaseBackupService $backups): BinaryFileResponse
    {
        $this->authorizeBackups();

        $path = $backups->path($filename);
        abort_if($path === null, 404);

        AuditService::log('admin.backup_downloaded', null, null, ['file' => $filename]);

        return response()->download($path, $filename, ['Content-Type' => 'application/gzip']);
    }

    protected function authorizeBackups(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Zaxira nusxalar faqat administrator uchun.');
        }
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

        $shipment = Shipment::with('suborder.order')->findOrFail($id);
        $courier = User::findOrFail($validated['courier_id']);

        if (!$courier->hasRole(['courier', 'logistics_operator'])) {
            return back()->with('error', "Tanlangan foydalanuvchi kuryer emas.");
        }
        $orderStatus = $shipment->suborder?->order?->status;
        if (in_array($orderStatus, ['cancelled', 'pending_payment'], true)
            || in_array($shipment->status, ['delivered', 'cancelled'], true)) {
            return back()->with('error', "Jo'natma #{$shipment->tracking_number}: "
                . ($orderStatus === 'pending_payment' ? "buyurtma hali to'lanmagan." : "buyurtma yopilgan yoki bekor qilingan.")
                . " Kuryer biriktirib bo'lmaydi.");
        }

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

    /**
     * Categories Management
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? '📦',
            'sort_order' => (int)($validated['sort_order'] ?? 0),
            'is_active' => (bool)$request->has('is_active'),
        ]);

        AuditService::log('admin.category_created', $category, null, ['name' => $category->name]);

        return redirect()->route('admin.dashboard', ['tab' => 'categories'])->with('success', "Yangi kategoriya ({$category->name}) muvaffaqiyatli yaratildi.");
    }

    public function updateCategoryStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        AuditService::log('admin.category_status_toggled', $category, null, ['is_active' => $category->is_active]);

        $statusStr = $category->is_active ? 'faollashtirildi' : 'nofaol qilindi';
        return redirect()->route('admin.dashboard', ['tab' => 'categories'])->with('success', "Kategoriya ({$category->name}) {$statusStr}.");
    }

    public function deleteCategory(int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $category = Category::withCount('products')->findOrFail($id);
        if ($category->products_count > 0) {
            $category->update(['is_active' => false]);
            return redirect()->route('admin.dashboard', ['tab' => 'categories'])->with('error', "Kategoriyada {$category->products_count} ta mahsulot borligi sababli u o'chirilmadi, balki nofaol holatga o'tkazildi.");
        }

        $name = $category->name;
        $category->delete();

        AuditService::log('admin.category_deleted', null, null, ['name' => $name]);

        return redirect()->route('admin.dashboard', ['tab' => 'categories'])->with('success', "Kategoriya ({$name}) o'chirildi.");
    }

    /**
     * Marketing & Promocodes
     */
    public function storePromocode(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        // Codes are matched in upper case at checkout, so check uniqueness in upper case too
        // ("sinov10" next to "SINOV10" used to crash with a database error)
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', 'regex:/^[A-Z0-9_-]+$/', 'unique:promocodes,code'],
            'title' => ['nullable', 'string', 'max:150'],
            'type' => ['required', 'in:fixed,percentage'],
            'value' => ['required', 'integer', 'min:1', $request->input('type') === 'percentage' ? 'max:100' : 'max:100000000'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'max_discount_amount' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ], [
            'code.unique' => 'Bunday promokod allaqachon bor.',
            'code.regex' => "Promokodda faqat lotin harflari, raqamlar, - va _ bo'lsin.",
            'value.max' => $request->input('type') === 'percentage'
                ? "Foizli chegirma 100% dan oshmasin."
                : "Chegirma summasi juda katta.",
            'expires_at.after_or_equal' => "Tugash sanasi o'tib ketgan.",
        ]);

        $promocode = Promocode::create([
            'code' => strtoupper(trim($validated['code'])),
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'value' => (int)$validated['value'],
            'min_order_amount' => (int)($validated['min_order_amount'] ?? 0),
            'max_discount_amount' => !empty($validated['max_discount_amount']) ? (int)$validated['max_discount_amount'] : null,
            'usage_limit' => !empty($validated['usage_limit']) ? (int)$validated['usage_limit'] : null,
            'expires_at' => !empty($validated['expires_at']) ? $validated['expires_at'] : null,
            'is_active' => (bool)$request->has('is_active'),
        ]);

        AuditService::log('admin.promocode_created', $promocode, null, ['code' => $promocode->code]);

        return redirect()->route('admin.dashboard', ['tab' => 'marketing'])->with('success', "Promokod ({$promocode->code}) muvaffaqiyatli yaratildi.");
    }

    public function togglePromocode(int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $promo = Promocode::findOrFail($id);
        $promo->update(['is_active' => !$promo->is_active]);

        $statusStr = $promo->is_active ? 'faollashtirildi' : 'to\'xtatildi';
        return redirect()->route('admin.dashboard', ['tab' => 'marketing'])->with('success', "Promokod ({$promo->code}) {$statusStr}.");
    }

    public function deletePromocode(int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $promo = Promocode::findOrFail($id);
        $code = $promo->code;
        $promo->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'marketing'])->with('success', "Promokod ({$code}) o'chirildi.");
    }

    /**
     * Marketing & Banners
     */
    public function storeBanner(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            // Only real web addresses or site paths: "javascript:..." in a banner link runs code for every visitor
            'image_url' => ['required', 'string', 'max:500', 'regex:#^(https?://|/)[^\s<>"\']*$#i'],
            'link_url' => ['nullable', 'string', 'max:500', 'regex:#^(https?://|/)[^\s<>"\']*$#i'],
            'position' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ], [
            'image_url.regex' => "Rasm manzili https:// yoki / bilan boshlansin.",
            'link_url.regex' => "Havola https:// yoki / bilan boshlansin.",
        ]);

        $banner = Banner::create([
            'title' => $validated['title'],
            'image_url' => $validated['image_url'],
            'link_url' => $validated['link_url'] ?? '/',
            'position' => $validated['position'] ?? 'home_hero',
            'sort_order' => (int)($validated['sort_order'] ?? 0),
            'is_active' => (bool)$request->has('is_active'),
        ]);

        AuditService::log('admin.banner_created', $banner, null, ['title' => $banner->title]);

        return redirect()->route('admin.dashboard', ['tab' => 'marketing'])->with('success', "Yangi aksiya banneri ({$banner->title}) qo'shildi.");
    }

    public function updateBannerStatus(int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $banner = Banner::findOrFail($id);
        $banner->update(['is_active' => !$banner->is_active]);

        return redirect()->route('admin.dashboard', ['tab' => 'marketing'])->with('success', "Banner holati yangilandi.");
    }

    public function deleteBanner(int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $banner = Banner::findOrFail($id);
        $title = $banner->title;
        $banner->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'marketing'])->with('success', "Banner ({$title}) o'chirildi.");
    }

    /**
     * CSV / Excel Export
     */
    public function exportOrdersCsv(): StreamedResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $fileName = 'buyurtmalar_' . date('Y_m_d_His') . '.csv';
        $orders = Order::with(['user'])->latest()->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Microsoft Excel

            fputcsv($handle, ['Buyurtma Raqami', 'Xaridor Ismi', 'Telefon', 'Holati', 'Promokod', 'Chegirma (UZS)', 'Mahsulotlar (UZS)', 'Yetkazish (UZS)', 'Jami Summa (UZS)', 'Sana']);

            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->order_number,
                    $o->user?->name ?? 'Mehmon',
                    $o->shipping_address['phone'] ?? $o->user?->phone ?? '',
                    $o->status,
                    $o->promocode ?? '-',
                    $o->discount_total ?? 0,
                    $o->items_total,
                    $o->shipping_total,
                    $o->grand_total,
                    $o->created_at?->format('d.m.Y H:i') ?? '',
                ]);
            }
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSellersCsv(): StreamedResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403);
        }

        $fileName = 'sotuvchilar_' . date('Y_m_d_His') . '.csv';
        $sellers = Seller::withCount('products')->get();
        $ledger = app(SellerLedgerService::class);

        return response()->streamDownload(function () use ($sellers, $ledger) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Do\'kon Nomi', 'Yuridik Nomi', 'STIR (INN)', 'Holati', 'Komissiya %', 'Reyting', 'Mahsulotlar Soni', 'Joriy Balans (UZS)', 'Telefon', 'Email']);

            foreach ($sellers as $s) {
                $balance = $ledger->getSellerBalance($s->id);
                fputcsv($handle, [
                    $s->id,
                    $s->name,
                    $s->legal_name ?? '',
                    $s->tax_id ?? '',
                    $s->status,
                    $s->commission_rate . '%',
                    $s->rating,
                    $s->products_count,
                    $balance,
                    $s->phone ?? '',
                    $s->email ?? '',
                ]);
            }
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
