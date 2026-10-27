<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\SellerMember;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Unified Customer, Seller, and Courier Profile & Control Center.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        // If not logged in, redirect to login page
        if (!$user) {
            return redirect()->guest(route('login'))->with('error', "Profilingizni ko'rish uchun tizimga kiring.");
        }

        $isDemo = false;

        // Determine default tab based on user's primary role
        $defaultTab = 'orders';
        if ($user->isSeller()) {
            $defaultTab = 'seller_products';
        } elseif ($user->isCourier()) {
            $defaultTab = 'courier_shipments';
        }

        $activeTab = $request->query('tab', $defaultTab);

        // 1. Customer Orders
        $orders = Order::where('user_id', $user->id)
            ->with([
                'suborders.seller',
                'suborders.items',
                'suborders.shipments.courier',
                'latest_payment',
            ])
            ->latest()
            ->get();

        $activeOrdersCount = $orders->filter(fn($o) => 
            in_array($o->status, ['pending_payment', 'processing', 'awaiting_payment']) || 
            $o->suborders->flatMap->shipments->contains(fn($s) => in_array($s->status, ['in_transit', 'out_for_delivery']))
        )->count();

        // 2. Saved Addresses
        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        // 3. Saved Wishlist Products
        $savedProducts = $user->wishlistProducts()
            ->with(['seller:id,name,slug,rating', 'category:id,name,slug', 'variants.priceTiers'])
            ->get();

        // 4. Seller Context & Data (if seller)
        $seller = null;
        $sellerProducts = collect();
        $sellerSuborders = collect();
        $sellerWarehouses = collect();
        $sellerLedger = collect();
        $sellerBalance = 0;
        $categories = collect();
        $couriers = collect();

        if ($user->isSeller()) {
            $seller = $user->activeSeller() ?? Seller::where('id', $user->seller_id)->first();
            if (!$seller) {
                $membership = SellerMember::where('user_id', $user->id)->first();
                if ($membership) {
                    $seller = Seller::find($membership->seller_id);
                }
            }
            if (!$seller) {
                $seller = Seller::firstOrCreate(
                    ['slug' => Str::slug($user->name . '-store')],
                    [
                        'name' => $user->name . " Do'koni",
                        'legal_name' => $user->name . " XK",
                        'status' => 'active',
                        'commission_rate' => 5.0,
                        'rating' => 5.0,
                        'phone' => $user->phone,
                        'email' => $user->email,
                    ]
                );
                SellerMember::firstOrCreate(
                    ['seller_id' => $seller->id, 'user_id' => $user->id],
                    ['role' => 'owner', 'is_active' => true]
                );
                $user->update(['seller_id' => $seller->id]);
            }

            $sellerProducts = Product::where('seller_id', $seller->id)
                ->with(['category', 'variants.priceTiers', 'variants.inventoryBalances.warehouse'])
                ->latest()
                ->get();

            $sellerSuborders = Suborder::where('seller_id', $seller->id)
                ->with(['order.user', 'items.product', 'shipments.courier'])
                ->latest()
                ->limit(30)
                ->get();

            $sellerWarehouses = Warehouse::where('seller_id', $seller->id)
                ->with('balances.variant')
                ->get();

            $sellerLedger = SellerLedger::where('seller_id', $seller->id)->latest()->limit(20)->get();
            $credits = SellerLedger::where('seller_id', $seller->id)->where('type', 'credit')->sum('amount');
            $debits = SellerLedger::where('seller_id', $seller->id)->where('type', 'debit')->sum('amount');
            $sellerBalance = $credits - $debits;

            $categories = Category::where('is_active', true)->orderBy('name')->get();
            $couriers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['courier', 'logistics_operator']))->get();
        }

        // 5. Courier Context & Data (if courier)
        $courierShipments = collect();
        $courierActiveCount = 0;
        $courierDeliveredCount = 0;
        $courierTotalCashToCollect = 0;

        if ($user->isCourier()) {
            $courierShipments = Shipment::where('courier_id', $user->id)
                ->with([
                    'suborder.seller',
                    'suborder.order.user',
                    'suborder.order.payments',
                    'suborder.items.product',
                ])
                ->latest()
                ->get();

            $courierActiveCount = $courierShipments->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])->count();
            $courierDeliveredCount = $courierShipments->where('status', 'delivered')->count();

            $courierTotalCashToCollect = $courierShipments->filter(function ($s) {
                return $s->status !== 'delivered' && 
                       $s->suborder?->order?->latest_payment?->payment_method === 'cash_on_delivery';
            })->sum(fn($s) => $s->suborder?->total_amount ?? 0);
        }

        // 6. Superadmin / Admin Quick Stats
        $adminQuickStats = [];
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $adminQuickStats = [
                'total_users' => User::count(),
                'total_sellers' => Seller::count(),
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_couriers' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['courier', 'logistics_operator']))->count(),
            ];
        }

        return view('storefront.profile', compact(
            'user',
            'isDemo',
            'activeTab',
            'orders',
            'activeOrdersCount',
            'addresses',
            'savedProducts',
            'seller',
            'sellerProducts',
            'sellerSuborders',
            'sellerWarehouses',
            'sellerLedger',
            'sellerBalance',
            'categories',
            'couriers',
            'courierShipments',
            'courierActiveCount',
            'courierDeliveredCount',
            'courierTotalCashToCollect',
            'adminQuickStats'
        ));
    }

    /**
     * Seller: Add new product directly from profile without leaving the site.
     */
    public function storeSellerProduct(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSeller()) {
            abort(403, 'Faqat sotuvchilar mahsulot qo\'shishi mumkin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit' => ['required', 'in:piece,box,kg,ton'],
            'trade_mode' => ['required', 'in:wholesale_only,retail_only,both'],
            'price' => ['required', 'numeric', 'min:100'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:0.001'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $seller = $user->activeSeller() ?? Seller::where('id', $user->seller_id)->first() ?? Seller::first();

        // Warehouse setup
        $warehouse = Warehouse::firstOrCreate(
            ['seller_id' => $seller->id],
            ['name' => 'Asosiy Ombor', 'code' => 'WH-' . $seller->id . '-01', 'address' => 'Toshkent sh.', 'is_active' => true]
        );

        $slug = Str::slug($validated['name']);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(5));
        }

        // Image handling
        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('images/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = Str::slug($validated['name']) . '-' . time() . '.' . $extension;
            $file->move($uploadDir, $filename);
            $imageUrl = '/images/products/' . $filename;
        }

        if (!$imageUrl) {
            $category = Category::find($validated['category_id']);
            $catSlug = $category ? strtolower($category->slug . ' ' . $category->name) : '';
            if (str_contains($catSlug, 'sement')) {
                $imageUrl = '/images/products/sement.jpg';
            } elseif (str_contains($catSlug, 'quvur') || str_contains($catSlug, 'santexnika')) {
                $imageUrl = '/images/products/quvur.jpg';
            } elseif (str_contains($catSlug, 'elektr') || str_contains($catSlug, 'uskuna') || str_contains($catSlug, 'generator')) {
                $imageUrl = '/images/products/generator.jpg';
            } else {
                $imageUrl = '/images/products/sement.jpg';
            }
        }

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $validated['category_id'],
            'slug' => $slug,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trade_mode' => $validated['trade_mode'],
            'unit' => $validated['unit'],
            'min_order_quantity' => $validated['min_order_quantity'] ?? 1.0,
            'unit_step' => 1.0,
            'image_url' => $imageUrl,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $sku = 'SKU-' . strtoupper(Str::random(8));
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'name' => 'Standart',
            'status' => 'active',
        ]);

        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1,
            'unit_price' => (int) $validated['price'],
        ]);

        InventoryBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => $validated['stock_quantity'],
            'reserved_quantity' => 0,
        ]);

        AuditService::log('seller.product_created_from_profile', $product, null, [
            'seller_id' => $seller->id,
            'price' => $validated['price'],
            'stock' => $validated['stock_quantity'],
        ]);

        return redirect()->route('profile', ['tab' => 'seller_products'])
            ->with('success', "Mahsulot ({$product->name}) muvaffaqiyatli qo'shildi va do'koningizda sotuvga chiqdi!");
    }

    /**
     * Seller: Update product status (toggle active/hide or delete).
     */
    public function updateSellerProductStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSeller()) {
            abort(403, 'Ruxsat berilmagan.');
        }

        $seller = $user->activeSeller() ?? Seller::where('id', $user->seller_id)->first();
        $product = Product::where('id', $id)->where('seller_id', $seller->id)->firstOrFail();

        if ($request->has('toggle_active')) {
            $product->update(['is_active' => !$product->is_active]);
            $msg = $product->is_active ? 'Mahsulot yana do\'konda ko\'rinadigan bo\'ldi.' : 'Mahsulot vaqtincha yashirildi.';
            return back()->with('success', $msg);
        }

        if ($request->has('delete')) {
            $product->update(['is_active' => false, 'status' => 'rejected']);
            return back()->with('success', 'Mahsulot do\'kondan olib tashlandi.');
        }

        return back();
    }

    /**
     * Seller: Update incoming order status (confirm, pack, hand over to courier).
     */
    public function updateSellerOrderStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSeller()) {
            abort(403, 'Ruxsat berilmagan.');
        }

        $seller = $user->activeSeller() ?? Seller::where('id', $user->seller_id)->first();
        $suborder = Suborder::where('id', $id)->where('seller_id', $seller->id)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,packed,ready_for_shipping'],
            'courier_id' => ['nullable', 'exists:users,id'],
        ]);

        $suborder->update(['status' => $validated['status']]);

        // If shipment exists or needs creation for courier
        $shipment = Shipment::where('suborder_id', $suborder->id)->first();
        if (!$shipment) {
            $trackingNumber = 'TRK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $courierId = $validated['courier_id'] ?? User::whereHas('roles', fn($q) => $q->where('name', 'courier'))->value('id');

            $shipment = Shipment::create([
                'suborder_id' => $suborder->id,
                'tracking_number' => $trackingNumber,
                'courier_id' => $courierId,
                'status' => 'assigned',
                'shipping_method' => 'courier_express',
                'shipping_cost' => $suborder->shipping_cost ?? 20000,
                'pickup_address' => [
                    'store_name' => $seller->name,
                    'address' => $seller->address ?? 'Toshkent sh., Do\'kon ombori',
                    'phone' => $seller->phone ?? $user->phone,
                ],
                'delivery_address' => $suborder->order->shipping_address ?? [
                    'recipient_name' => $suborder->order->user?->name ?? 'Xaridor',
                    'address_line' => 'Toshkent shahri',
                    'phone' => $suborder->order->user?->phone ?? '',
                ],
                'estimated_delivery_at' => now()->addDay(),
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => 'assigned',
                'notes' => "Buyurtma do'kon tomonidan tasdiqlandi va kuryerga biriktirildi.",
                'payload' => [
                    'status' => 'assigned',
                    'location' => 'Sotuvchi ombori',
                ],
            ]);
        } elseif (!empty($validated['courier_id'])) {
            $shipment->update(['courier_id' => $validated['courier_id']]);
        }

        return redirect()->route('profile', ['tab' => 'seller_orders'])
            ->with('success', "Buyurtma #{$suborder->suborder_number} holati '{$validated['status']}' ga yangilandi!");
    }

    /**
     * Courier: Update shipment status (picked_up, in_transit, delivered, failed).
     */
    public function updateCourierShipmentStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isCourier()) {
            abort(403, 'Faqat kuryer yetkazish holatini o\'zgartirishi mumkin.');
        }

        $shipment = Shipment::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('courier_id', $user->id)
                  ->orWhereNull('courier_id');
            })
            ->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:picked_up,in_transit,delivered,failed'],
            'delay_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $status = $validated['status'];
        $shipment->status = $status;
        $shipment->courier_id = $user->id; // Ensure courier is assigned

        if ($status === 'delivered') {
            $shipment->delivered_at = now();

            // If cash on delivery, mark payment as paid
            $order = $shipment->suborder?->order;
            if ($order) {
                $payment = Payment::where('order_id', $order->id)->latest()->first();
                if ($payment && $payment->payment_method === 'cash_on_delivery') {
                    $payment->update([
                        'status' => 'paid',
                        'payload_snapshot' => array_merge($payment->payload_snapshot ?? [], [
                            'collected_by_courier_id' => $user->id,
                            'collected_at' => now()->toIso8601String(),
                        ]),
                    ]);
                }
                $order->update(['status' => 'completed']);
            }
        }

        if ($status === 'failed' && !empty($validated['delay_reason'])) {
            $shipment->delay_reason = $validated['delay_reason'];
        }

        $shipment->save();

        // Record tracking event
        $statusDescriptions = [
            'picked_up' => "Kuryer ({$user->name}) tovarlarni sotuvchi omboridan qabul qilib oldi.",
            'in_transit' => "Yetkazma yo'lda. Kuryer xaridor manziliga harakatlanmoqda.",
            'delivered' => "Yetkazma xaridorga topshirildi va buyurtma muvaffaqiyatli yakunlandi.",
            'failed' => "Yetkazib bo'lmadi: " . ($validated['delay_reason'] ?? "Mijoz bilan bog'lanib bo'lmadi."),
        ];

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'event_type' => $status,
            'notes' => $statusDescriptions[$status] ?? "Holat o'zgardi: {$status}",
            'payload' => [
                'status' => $status,
                'courier_id' => $user->id,
                'location' => 'Kuryer boshqaruvi',
                'occurred_at' => now()->toIso8601String(),
            ],
        ]);

        AuditService::log('courier.shipment_status_updated', $shipment, null, [
            'status' => $status,
            'courier_id' => $user->id,
        ]);

        return redirect()->route('profile', ['tab' => 'courier_shipments'])
            ->with('success', "Jo'natma #{$shipment->tracking_number} holati muvaffaqiyatli '{$status}' ga yangilandi!");
    }

    /**
     * Update customer personal profile (Name, Phone, Email).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Iltimos, tizimga kiring.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil ma\'lumotlari muvaffaqiyatli saqlandi.');
    }

    /**
     * Update customer account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Iltimos, tizimga kiring.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Joriy parol noto\'g\'ri kiritildi.']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Parolingiz muvaffaqiyatli yangilandi.');
    }

    /**
     * Add new delivery address.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            $user = User::where('email', 'customer@market.uz')->first();
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'full_address' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = $request->has('is_default');

        if ($isDefault) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        Address::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'full_address' => $validated['full_address'],
            'landmark' => $validated['landmark'] ?? null,
            'is_default' => $isDefault || Address::where('user_id', $user->id)->count() === 0,
        ]);

        return back()->with('success', 'Yangi yetkazib berish manzili qo\'shildi.');
    }

    /**
     * Delete an existing address.
     */
    public function deleteAddress(int $id): RedirectResponse
    {
        $user = Auth::user();
        $userId = $user ? $user->id : User::where('email', 'customer@market.uz')->value('id');

        $address = Address::where('id', $id)->where('user_id', $userId)->firstOrFail();
        $address->delete();

        return back()->with('success', 'Manzil muvaffaqiyatli o\'chirildi.');
    }

    /**
     * Set default address.
     */
    public function setDefaultAddress(int $id): RedirectResponse
    {
        $user = Auth::user();
        $userId = $user ? $user->id : User::where('email', 'customer@market.uz')->value('id');

        Address::where('user_id', $userId)->update(['is_default' => false]);
        Address::where('id', $id)->where('user_id', $userId)->update(['is_default' => true]);

        return back()->with('success', 'Asosiy yetkazib berish manzili belgilandi.');
    }
}
