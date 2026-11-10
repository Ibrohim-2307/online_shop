<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\SellerPayout;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Backup\DatabaseBackupService;
use App\Services\Ledger\SellerLedgerService;
use App\Services\Refund\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        $tabs = ['sellers', 'products', 'users', 'shipments', 'orders', 'refunds', 'audit'];
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
            'roles',
            'products',
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
