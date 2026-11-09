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
use App\Services\Checkout\InventoryReservationService;
use App\Services\Inventory\InventoryService;
use App\Services\Ledger\SellerLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $sellerNeedsActionCount = 0;
        $sellerSalesStats = [];

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
                        // A store created implicitly still needs admin approval before it can sell
                        'status' => 'pending',
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
                ->limit(100)
                ->get();

            // Orders the seller must act on now (paid or cash-on-delivery, not yet being packed) go to the top
            $needsAction = fn($sub) => in_array($sub->status, ['pending', 'confirmed'], true)
                && in_array($sub->order?->status, ['processing'], true);
            $sellerNeedsActionCount = $sellerSuborders->filter($needsAction)->count();
            $sellerSuborders = $sellerSuborders->sortBy(fn($sub) => $needsAction($sub) ? 0 : 1)->values();

            // Last 30 days of real sales (not cancelled, not awaiting online payment)
            $recentSales = Suborder::where('seller_id', $seller->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->where('status', '!=', 'cancelled')
                ->whereHas('order', fn($q) => $q->whereNotIn('status', ['cancelled', 'pending_payment']))
                ->get(['id', 'status', 'items_subtotal']);
            $topProducts = \App\Models\OrderItem::whereIn('suborder_id', $recentSales->pluck('id'))
                ->selectRaw('product_name, SUM(quantity) as qty, SUM(subtotal) as revenue')
                ->groupBy('product_name')
                ->orderByDesc('revenue')
                ->limit(5)
                ->get();
            $sellerSalesStats = [
                'revenue' => (int) $recentSales->sum('items_subtotal'),
                'orders' => $recentSales->count(),
                'delivered' => $recentSales->where('status', 'delivered')->count(),
                'average' => $recentSales->count() ? (int) round($recentSales->avg('items_subtotal')) : 0,
                'top_products' => $topProducts,
                'out_of_stock' => $sellerProducts->filter(fn($p) => $p->is_active
                    && $p->variants->flatMap->inventoryBalances->sum(fn($b) => (float) $b->quantity - (float) $b->reserved_quantity) <= 0)->count(),
            ];

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
        $courierActiveShipments = collect();
        $courierFinishedShipments = collect();
        $courierActiveCount = 0;
        $courierDeliveredCount = 0;
        $courierTotalCashToCollect = 0;
        $courierStats = [];

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

            $isCod = fn($s) => $s->suborder?->order?->latest_payment?->payment_method === 'cash_on_delivery';
            $isCancelled = fn($s) => $s->suborder?->order?->status === 'cancelled' || $s->suborder?->status === 'cancelled';

            // Work to do now: on the road first, then picked up, then waiting at the store
            $priority = ['in_transit' => 0, 'out_for_delivery' => 0, 'picked_up' => 1, 'failed' => 2, 'assigned' => 3, 'pending' => 4];
            $courierActiveShipments = $courierShipments
                ->filter(fn($s) => !in_array($s->status, ['delivered'], true) && !$isCancelled($s))
                ->sortBy(fn($s) => $priority[$s->status] ?? 9)
                ->values();
            $courierFinishedShipments = $courierShipments
                ->filter(fn($s) => $s->status === 'delivered' || $isCancelled($s))
                ->values();

            $courierActiveCount = $courierActiveShipments->count();
            $courierDeliveredCount = $courierShipments->where('status', 'delivered')->count();
            $courierTotalCashToCollect = $courierActiveShipments->filter($isCod)->sum(fn($s) => $s->suborder?->total_amount ?? 0);

            $deliveredToday = $courierShipments->filter(fn($s) => $s->status === 'delivered' && $s->delivered_at?->isToday());
            $courierStats = [
                'active' => $courierActiveCount,
                'delivered_today' => $deliveredToday->count(),
                'cash_to_collect' => $courierTotalCashToCollect,
                'cash_collected_today' => $deliveredToday->filter($isCod)->sum(fn($s) => $s->suborder?->total_amount ?? 0),
            ];
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
            'sellerNeedsActionCount',
            'sellerSalesStats',
            'courierShipments',
            'courierActiveShipments',
            'courierFinishedShipments',
            'courierStats',
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
            'tier_300_price' => ['nullable', 'integer', 'min:100'],
            'tier_1000_price' => ['nullable', 'integer', 'min:100'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:0.001'],
            'unit_step' => ['nullable', 'numeric', 'min:0.001'],
            'items_per_box' => ['nullable', 'integer', 'min:1'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            // No SVG: an uploaded SVG is served from our domain and can carry scripts
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000', 'regex:#^(/images/|/uploads/|https://)#'],
        ]);

        // Never fall back to someone else's store
        $seller = $this->currentSeller($user);
        if (!$seller) {
            return back()->with('error', "Hisobingizga do'kon biriktirilmagan. Administrator bilan bog'laning.");
        }

        if ($seller->status === 'suspended') {
            return back()->with('error', "Do'koningiz faoliyati vaqtincha to'xtatilgan (bloklangan). Yangi mahsulot qo'shib bo'lmaydi.");
        }

        // Warehouse setup
        $warehouse = Warehouse::firstOrCreate(
            ['seller_id' => $seller->id],
            ['name' => 'Asosiy Ombor', 'code' => 'WH-' . $seller->id . '-01', 'address' => 'Toshkent sh.', 'is_active' => true]
        );

        $slug = Str::slug($validated['name']);
        if (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(5));
        }

        // Image handling
        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image_file')) {
            $imageUrl = $this->storeProductImage($request->file('image_file'), $validated['name']);
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
            'unit_step' => $validated['unit_step'] ?? 1.0,
            'items_per_box' => $validated['items_per_box'] ?? null,
            'image_url' => $imageUrl,
            // A store the admin hasn't approved yet can prepare products, but they go live only after moderation
            'status' => $seller->status === 'active' ? 'approved' : 'pending',
            'is_active' => true,
        ]);

        $sku = !empty($validated['sku']) ? $validated['sku'] : ('SKU-' . strtoupper(Str::random(8)));
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'name' => 'Standart',
            'is_active' => true,
        ]);

        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => (float) ($validated['min_order_quantity'] ?? 1),
            'unit_price' => (int) $validated['price'],
            'currency' => 'UZS',
        ]);

        if (!empty($validated['tier_300_price'])) {
            PriceTier::create([
                'product_variant_id' => $variant->id,
                'min_quantity' => 300.0,
                'max_quantity' => 999.0,
                'unit_price' => (int) $validated['tier_300_price'],
                'currency' => 'UZS',
            ]);
        }

        if (!empty($validated['tier_1000_price'])) {
            PriceTier::create([
                'product_variant_id' => $variant->id,
                'min_quantity' => 1000.0,
                'max_quantity' => null,
                'unit_price' => (int) $validated['tier_1000_price'],
                'currency' => 'UZS',
            ]);
        }

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
            ->with('success', $product->status === 'approved'
                ? "Mahsulot ({$product->name}) muvaffaqiyatli qo'shildi va do'koningizda sotuvga chiqdi!"
                : "Mahsulot ({$product->name}) saqlandi. Do'koningiz tasdiqlangach saytda ko'rinadi.");
    }

    /**
     * Seller: edit an existing product — name, description, retail price, stock on hand, minimum order, photo.
     */
    public function updateSellerProduct(Request $request, int $id, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSeller()) {
            abort(403, 'Ruxsat berilmagan.');
        }

        $seller = $this->currentSeller($user);
        abort_unless($seller, 403);
        $product = Product::where('id', $id)->where('seller_id', $seller->id)->with('variants.priceTiers')->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'trade_mode' => ['nullable', 'in:both,retail_only,wholesale_only,retail,wholesale'],
            'unit' => ['nullable', 'in:piece,box,kg,ton'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:0.001'],
            'unit_step' => ['nullable', 'numeric', 'min:0.001'],
            'items_per_box' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:100'],
            'tier_300_price' => ['nullable', 'integer', 'min:100'],
            'tier_1000_price' => ['nullable', 'integer', 'min:100'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000', 'regex:#^(/images/|/uploads/|https://)#'],
        ]);

        $variant = $product->variants->first();
        abort_unless($variant, 404);

        $warehouse = Warehouse::firstOrCreate(
            ['seller_id' => $seller->id],
            ['name' => 'Asosiy Ombor', 'code' => 'WH-' . $seller->id . '-01', 'address' => 'Toshkent sh.', 'is_active' => true]
        );
        $balance = InventoryBalance::where('warehouse_id', $warehouse->id)->where('product_variant_id', $variant->id)->first();
        $reserved = (float) ($balance?->reserved_quantity ?? 0);

        if ((float) $validated['stock_quantity'] < $reserved) {
            return back()->with('error', "Qoldiq {$reserved} dan kam bo'lishi mumkin emas: shuncha tovar mijozlar buyurtmalari uchun band qilingan.");
        }

        DB::transaction(function () use ($request, $product, $variant, $validated, $warehouse, $balance, $inventoryService, $user) {
            $updates = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'min_order_quantity' => $validated['min_order_quantity'] ?? $product->min_order_quantity,
            ];
            if (!empty($validated['category_id'])) {
                $updates['category_id'] = $validated['category_id'];
            }
            if (!empty($validated['trade_mode'])) {
                $updates['trade_mode'] = $validated['trade_mode'];
            }
            if (!empty($validated['unit'])) {
                $updates['unit'] = $validated['unit'];
            }
            if (isset($validated['items_per_box'])) {
                $updates['items_per_box'] = $validated['items_per_box'];
            }
            if (isset($validated['unit_step'])) {
                $updates['unit_step'] = $validated['unit_step'];
            }
            if ($request->hasFile('image_file')) {
                $updates['image_url'] = $this->storeProductImage($request->file('image_file'), $validated['name']);
            } elseif (!empty($validated['image_url'])) {
                $updates['image_url'] = $validated['image_url'];
            }
            $product->update($updates);

            // Update SKU if provided
            if (!empty($validated['sku'])) {
                $variant->update(['sku' => $validated['sku']]);
            }

            // Retail base price (Tier 1)
            $baseTier = $variant->priceTiers->sortBy('min_quantity')->first();
            if ($baseTier) {
                $baseTier->update([
                    'unit_price' => (int) $validated['price'],
                    'min_quantity' => (float) ($validated['min_order_quantity'] ?? $baseTier->min_quantity),
                ]);
            } else {
                PriceTier::create([
                    'product_variant_id' => $variant->id,
                    'min_quantity' => (float) ($validated['min_order_quantity'] ?? 1),
                    'unit_price' => (int) $validated['price'],
                    'currency' => 'UZS',
                ]);
            }

            // Wholesale Tier 2 (300+)
            $tier300 = $variant->priceTiers->first(fn($t) => (float)$t->min_quantity >= 300 && (float)$t->min_quantity < 1000);
            if (!empty($validated['tier_300_price'])) {
                if ($tier300) {
                    $tier300->update(['unit_price' => (int) $validated['tier_300_price']]);
                } else {
                    PriceTier::create([
                        'product_variant_id' => $variant->id,
                        'min_quantity' => 300.0,
                        'max_quantity' => 999.0,
                        'unit_price' => (int) $validated['tier_300_price'],
                        'currency' => 'UZS',
                    ]);
                }
            } elseif ($tier300 && array_key_exists('tier_300_price', $validated) && $validated['tier_300_price'] === null) {
                $tier300->delete();
            }

            // Wholesale Tier 3 (1000+)
            $tier1000 = $variant->priceTiers->first(fn($t) => (float)$t->min_quantity >= 1000);
            if (!empty($validated['tier_1000_price'])) {
                if ($tier1000) {
                    $tier1000->update(['unit_price' => (int) $validated['tier_1000_price']]);
                } else {
                    PriceTier::create([
                        'product_variant_id' => $variant->id,
                        'min_quantity' => 1000.0,
                        'max_quantity' => null,
                        'unit_price' => (int) $validated['tier_1000_price'],
                        'currency' => 'UZS',
                    ]);
                }
            } elseif ($tier1000 && array_key_exists('tier_1000_price', $validated) && $validated['tier_1000_price'] === null) {
                $tier1000->delete();
            }

            $delta = bcsub((string) $validated['stock_quantity'], (string) ($balance?->quantity ?? 0), 3);
            if (bccomp($delta, '0', 3) !== 0) {
                $inventoryService->adjustStock($warehouse, $variant, $delta, 'adjustment', "Sotuvchi qoldiqni yangiladi", $user);
            }
        });

        AuditService::log('seller.product_updated', $product, null, [
            'price' => (int) $validated['price'],
            'stock' => $validated['stock_quantity'],
        ]);

        return redirect()->route('profile', ['tab' => 'seller_products'])
            ->with('success', "Mahsulot ({$product->name}) yangilandi.");
    }

    /**
     * The store the signed-in seller works for, or null. Never another store.
     */
    protected function currentSeller(User $user): ?Seller
    {
        return $user->activeSeller()
            ?? ($user->seller_id ? Seller::find($user->seller_id) : null);
    }

    /**
     * Save an uploaded product photo under public/images/products with a server-chosen name and extension.
     */
    protected function storeProductImage(\Illuminate\Http\UploadedFile $file, string $name): string
    {
        $uploadDir = public_path('images/products');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $extension = in_array($file->guessExtension(), ['jpg', 'jpeg', 'png', 'webp'], true) ? $file->guessExtension() : 'jpg';
        $filename = Str::slug($name) . '-' . time() . '-' . Str::lower(Str::random(4)) . '.' . $extension;
        $file->move($uploadDir, $filename);

        return '/images/products/' . $filename;
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

        $seller = $this->currentSeller($user);
        abort_unless($seller, 403);
        $product = Product::where('id', $id)->where('seller_id', $seller->id)->firstOrFail();

        if ($request->has('toggle_active')) {
            $product->update(['is_active' => !$product->is_active]);
            $msg = $product->is_active ? 'Mahsulot yana sotuvga chiqarildi.' : 'Mahsulot muzlatildi: xaridorlarga ko\'rinmaydi.';
            return back()->with('success', $msg);
        }

        if ($request->has('delete')) {
            $variantIds = $product->variants()->pluck('id');

            // Goods already promised to customers must be delivered (or the orders cancelled) first
            $openReservations = \App\Models\InventoryReservation::whereIn('product_variant_id', $variantIds)
                ->whereIn('status', ['reserved', 'committed'])
                ->exists();
            if ($openReservations) {
                return back()->with('error', "\"{$product->name}\" faol buyurtmalarda bor. Avval ularni yetkazing yoki bekor qiling, keyin o'chiring.");
            }

            DB::transaction(function () use ($product, $variantIds, $user) {
                // Drop it from shoppers' carts so nobody tries to buy a product that no longer exists
                \App\Models\CartItem::whereIn('product_variant_id', $variantIds)->delete();
                $product->update(['is_active' => false]);
                // Soft delete: hidden everywhere, but order history keeps referring to it
                $product->delete();

                AuditService::log('seller.product_deleted', $product, null, ['name' => $product->name], $user);
            });

            return redirect()->route('profile', ['tab' => 'seller_products'])
                ->with('success', "\"{$product->name}\" butunlay o'chirildi.");
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

        $seller = $this->currentSeller($user);
        abort_unless($seller, 403);
        $suborder = Suborder::where('id', $id)->where('seller_id', $seller->id)->firstOrFail();

        $validated = $request->validate([
            // "packed" is kept for old forms; it is the same stage as "preparing"
            'status' => ['required', 'in:confirmed,preparing,packed,ready_for_shipping'],
            'courier_id' => ['nullable', 'integer'],
        ]);

        // Only orders that are actually going ahead may be worked on
        if (in_array($suborder->status, ['cancelled', 'shipped', 'delivered'], true) || $suborder->order->status === 'cancelled') {
            return redirect()->route('profile', ['tab' => 'seller_orders'])
                ->with('error', "Buyurtma #{$suborder->suborder_number} holatini endi o'zgartirib bo'lmaydi.");
        }
        if ($suborder->order->status === 'pending_payment') {
            return redirect()->route('profile', ['tab' => 'seller_orders'])
                ->with('error', "Buyurtma #{$suborder->suborder_number} hali to'lanmagan. To'lov tushgach tayyorlashni boshlang.");
        }

        $courierId = null;
        if (!empty($validated['courier_id'])) {
            $courierId = User::where('id', $validated['courier_id'])
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['courier', 'logistics_operator']))
                ->value('id');
            if (!$courierId) {
                return back()->with('error', "Tanlangan foydalanuvchi kuryer emas.");
            }
        }

        // Suborder status column only knows: pending, confirmed, preparing, shipped, delivered, cancelled
        $newStatus = $validated['status'] === 'confirmed' ? 'confirmed' : 'preparing';
        $suborder->update(['status' => $newStatus]);

        $shipment = Shipment::where('suborder_id', $suborder->id)->first();

        if ($validated['status'] === 'ready_for_shipping' && !$courierId && !$shipment?->courier_id) {
            return redirect()->route('profile', ['tab' => 'seller_orders'])
                ->with('error', "Kuryerga berish uchun kuryerni tanlang.");
        }

        if (!$shipment) {
            $trackingNumber = 'TRK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $shipment = Shipment::create([
                'suborder_id' => $suborder->id,
                'tracking_number' => $trackingNumber,
                'courier_id' => $courierId,
                'status' => $courierId ? 'assigned' : 'pending',
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
                'event_type' => $shipment->status,
                'notes' => $courierId
                    ? "Buyurtma do'kon tomonidan tasdiqlandi va kuryerga biriktirildi."
                    : "Buyurtma do'kon tomonidan tasdiqlandi.",
                'payload' => [
                    'status' => $shipment->status,
                    'location' => 'Sotuvchi ombori',
                ],
            ]);
        } elseif ($courierId && in_array($shipment->status, ['pending', 'assigned'], true)) {
            $shipment->update(['courier_id' => $courierId, 'status' => 'assigned']);
        }

        $labels = [
            'confirmed' => 'Tasdiqlandi',
            'preparing' => 'Qadoqlanmoqda',
            'packed' => 'Qadoqlanmoqda',
            'ready_for_shipping' => 'Kuryerga berishga tayyor',
        ];

        return redirect()->route('profile', ['tab' => 'seller_orders'])
            ->with('success', "Buyurtma #{$suborder->suborder_number}: {$labels[$validated['status']]}.");
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
            'delay_reason' => ['nullable', 'string', 'max:255', 'required_if:status,failed'],
        ], [
            'delay_reason.required_if' => "Nima uchun yetkazib bo'lmaganini tanlang.",
        ]);

        $status = $validated['status'];
        $suborder = $shipment->suborder;
        $order = $suborder?->order;

        if ($order && in_array($order->status, ['cancelled', 'pending_payment'], true)) {
            return redirect()->route('profile', ['tab' => 'courier_shipments'])
                ->with('error', $order->status === 'cancelled'
                    ? "Bu buyurtma mijoz tomonidan bekor qilingan. Tovarni olmang."
                    : "Bu buyurtma hali to'lanmagan. Sotuvchi tayyorlaguncha kuting.");
        }
        if ($shipment->status === 'delivered') {
            return redirect()->route('profile', ['tab' => 'courier_shipments'])
                ->with('error', "Jo'natma #{$shipment->tracking_number} allaqachon yetkazilgan.");
        }

        DB::transaction(function () use ($shipment, $suborder, $order, $status, $validated, $user) {
            $shipment->status = $status;
            $shipment->courier_id = $user->id; // Ensure courier is assigned

            if ($status === 'failed' && !empty($validated['delay_reason'])) {
                $shipment->delay_reason = $validated['delay_reason'];
            }

            if (in_array($status, ['picked_up', 'in_transit', 'delivered'], true) && $suborder) {
                // Goods left the seller's warehouse: deduct stock once
                app(InventoryReservationService::class)->fulfillForSuborder($suborder, $user->id);
                if (!in_array($suborder->status, ['shipped', 'delivered'], true)) {
                    $suborder->update(['status' => 'shipped']);
                }
            }

            if ($status === 'delivered' && $suborder && $order) {
                $shipment->delivered_at = now();
                $suborder->update(['status' => 'delivered']);

                $payment = Payment::where('order_id', $order->id)->latest()->first();
                $isCod = $payment && $payment->payment_method === 'cash_on_delivery';

                // Online payments credited the seller at payment time; cash is credited when the courier collects it
                if ($isCod) {
                    $alreadyCredited = SellerLedger::where('suborder_id', $suborder->id)
                        ->where('entry_type', 'order_sale')
                        ->exists();
                    if (!$alreadyCredited) {
                        app(SellerLedgerService::class)->recordOrderSale($suborder);
                    }

                    $snapshot = $payment->payload_snapshot ?? [];
                    $snapshot['collected'][] = [
                        'suborder_id' => $suborder->id,
                        'amount' => (int) $suborder->total_amount,
                        'courier_id' => $user->id,
                        'collected_at' => now()->toIso8601String(),
                    ];
                    $snapshot['collected_by_courier_id'] = $user->id;
                    $snapshot['collected_at'] = now()->toIso8601String();
                    $payment->payload_snapshot = $snapshot;
                }

                // The whole order is complete only when every seller's part has arrived
                $allDelivered = $order->suborders()->where('id', '!=', $suborder->id)
                    ->whereNotIn('status', ['delivered', 'cancelled'])
                    ->doesntExist();

                if ($allDelivered) {
                    $order->update(['status' => 'completed']);
                    if ($isCod) {
                        $payment->status = 'paid';
                    }
                }

                if ($isCod) {
                    $payment->save();
                }
            }

            $shipment->save();
        });

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
            ->with('success', "Jo'natma #{$shipment->tracking_number}: " . [
                'picked_up' => "do'kondan olindi",
                'in_transit' => "yo'lda",
                'delivered' => 'xaridorga topshirildi',
                'failed' => "yetkazib bo'lmadi deb belgilandi",
            ][$status] . '.');
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
