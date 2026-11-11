<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Address;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService
    ) {}
    /**
     * Home page with hero banner, categories, featured products, and verified sellers.
     */
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount(['products' => function ($query) {
                $query->where('status', 'approved')->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->take(14)
            ->get();

        $featuredProducts = Product::where('status', 'approved')
            ->where('is_active', true)
            ->fromActiveSeller()->inStock()
            ->with(['seller:id,name,slug,rating', 'category:id,name,slug', 'variants.priceTiers'])
            ->latest()
            ->take(8)
            ->get();

        $wholesaleProducts = Product::where('status', 'approved')
            ->where('is_active', true)
            ->fromActiveSeller()->inStock()
            ->whereIn('trade_mode', ['wholesale_only', 'both'])
            ->with(['seller:id,name,slug,rating', 'category:id,name,slug', 'variants.priceTiers'])
            ->take(6)
            ->get();

        $sellers = Seller::where('status', 'active')
            ->withCount(['products' => fn($q) => $q->where('status', 'approved')])
            ->orderByDesc('rating')
            ->take(4)
            ->get();

        return view('storefront.index', compact('categories', 'featuredProducts', 'wholesaleProducts', 'sellers'));
    }

    /**
     * Catalog and Search page with category, trade mode, and query filters.
     * Query string is preserved on pagination.
     */
    public function catalog(Request $request): View
    {
        $query = Product::where('status', 'approved')->where('is_active', true)->fromActiveSeller()->inStock();

        // Filter by Search Query
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Category
        $selectedCategory = null;
        if ($catSlug = $request->input('category')) {
            $selectedCategory = Category::where('slug', $catSlug)->first();
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        // Filter by Trade Mode (retail_only, wholesale_only, all)
        $tradeMode = $request->input('trade_mode', 'all');
        if ($tradeMode === 'retail_only') {
            $query->whereIn('trade_mode', ['retail_only', 'both']);
        } elseif ($tradeMode === 'wholesale_only') {
            $query->whereIn('trade_mode', ['wholesale_only', 'both']);
        }

        // Filter by Seller
        if ($sellerSlug = $request->input('seller')) {
            $query->whereHas('seller', fn($sq) => $sq->where('slug', $sellerSlug));
        }

        // Retail (single-unit) price = base tier (lowest min_quantity) of the cheapest active variant
        $retailPriceSql = '(SELECT MIN(pt.unit_price) FROM price_tiers pt
            JOIN product_variants pv ON pv.id = pt.product_variant_id
            WHERE pv.product_id = products.id AND pv.is_active = 1
              AND pt.min_quantity = (SELECT MIN(pt2.min_quantity) FROM price_tiers pt2 WHERE pt2.product_variant_id = pv.id))';

        // Filter by price range
        if ($minPrice = $request->integer('min_price')) {
            $query->whereRaw("{$retailPriceSql} >= ?", [$minPrice]);
        }
        if ($maxPrice = $request->integer('max_price')) {
            $query->whereRaw("{$retailPriceSql} <= ?", [$maxPrice]);
        }

        // Sort
        $query->select('products.*')->selectRaw("{$retailPriceSql} as min_price");

        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderByRaw('min_price IS NULL')->orderBy('min_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('min_price', 'desc');
                break;
            case 'rating':
                $query->withAvg(['reviews' => fn($rq) => $rq->where('is_approved', true)], 'rating')
                    ->orderByDesc('reviews_avg_rating');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->with(['seller:id,name,slug,rating', 'category:id,name,slug', 'variants.priceTiers'])
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->get();

        return view('storefront.catalog', compact('products', 'categories', 'selectedCategory', 'tradeMode'));
    }

    /**
     * Live search endpoint returning JSON suggestions for instant autocomplete.
     */
    public function liveSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('status', 'approved')
            ->where('is_active', true)
            ->fromActiveSeller()->inStock()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhereHas('variants', function ($vq) use ($q) {
                          $vq->where('sku', 'like', "%{$q}%")
                             ->orWhere('name', 'like', "%{$q}%");
                      });
            })
            ->with(['category:id,name,slug', 'defaultVariant.priceTiers'])
            ->take(6)
            ->get()
            ->map(function ($product) {
                $baseTier = $product->defaultVariant?->priceTiers->sortBy('min_quantity')->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'url' => route('product.show', $product->slug),
                    'category' => $product->category?->name,
                    'price' => $baseTier ? number_format($baseTier->unit_price, 0, '.', ' ') . ' UZS' : null,
                    'trade_mode' => $product->trade_mode,
                ];
            });

        return response()->json(['products' => $products]);
    }

    /**
     * Product details page with variant switching, all-units tiered price calculation, and stock.
     */
    public function product(string $slug): View
    {
        // The product page stays reachable when frozen (out of stock) and says so instead of 404-ing old links
        $product = Product::where('slug', $slug)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->fromActiveSeller()
            ->with([
                'seller:id,name,slug,rating,phone,email,address',
                'category:id,name,slug',
                'variants.priceTiers',
                'variants.inventoryBalances.warehouse:id,name,address',
                'reviews' => fn($q) => $q->where('is_approved', true)->latest()->take(25),
            ])
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->fromActiveSeller()->inStock()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->with(['seller:id,name,slug,rating', 'variants.priceTiers'])
            ->take(4)
            ->get();

        return view('storefront.product', compact('product', 'relatedProducts'));
    }

    /**
     * Submit a customer review and star rating for a product.
     */
    public function storeReview(Request $request, string $slug): RedirectResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Only signed-in customers may review, so ratings can't be flooded anonymously
        $user = $request->user();
        if (!$user) {
            return redirect()->guest(route('login'))
                ->with('error', "Sharh qoldirish uchun tizimga kiring.");
        }

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'author_name' => 'nullable|string|min:2|max:100',
            'comment' => 'required|string|min:3|max:1000',
        ]);

        $alreadyReviewed = \App\Models\ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadyReviewed) {
            return back()->with('error', "Siz bu mahsulotga allaqachon sharh qoldirgansiz.");
        }

        $isVerifiedPurchase = \App\Models\OrderItem::where('product_id', $product->id)
            ->whereHas('suborder.order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereIn('status', ['processing', 'completed']);
            })
            ->exists();

        \App\Models\ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'author_name' => $validated['author_name'] ?? $user->name,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => $isVerifiedPurchase,
            'is_approved' => true,
        ]);

        return back()->with('success_review', "Fikr va bahoingiz uchun tashakkur! Sharhingiz sahifada e'lon qilindi.");
    }

    /**
     * Seller public storefront profile and their product listings.
     */
    public function seller(string $slug): View
    {
        $seller = Seller::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $products = Product::where('seller_id', $seller->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->inStock()
            ->with(['variants.priceTiers', 'category:id,name,slug'])
            ->paginate(12);

        return view('storefront.seller', compact('seller', 'products'));
    }

    /**
     * Customer wishlist / favorites page.
     */
    public function wishlist(): View
    {
        return view('storefront.wishlist');
    }

    /**
     * Customer cart view with live multi-vendor quote.
     */
    public function cart(Request $request): View
    {
        $user = $request->user();
        $sessionId = $this->cartSessionId($request);

        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);

        $quote = $this->cartService->getCartQuote($cart);

        return view('storefront.cart', compact('cart', 'quote'));
    }

    /**
     * Web add item to cart.
     */
    public function addToCart(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $user = $request->user();
        $sessionId = $this->cartSessionId($request);

        try {
            $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
            $item = $this->cartService->addItem($cart, $validated['variant_id'], (float) $validated['quantity']);
            $quote = $this->cartService->getCartQuote($cart);

            $cookie = cookie('cart_session_id', $sessionId, 60 * 24 * 30);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Mahsulot savatchaga muvaffaqiyatli qo'shildi.",
                    'data' => [
                        'cart_item_id' => $item->id,
                        'session_id' => $sessionId,
                        'quote' => $quote,
                    ]
                ], 200)->withCookie($cookie);
            }

            return redirect()->route('cart')
                ->with('success', "Mahsulot savatchaga muvaffaqiyatli qo'shildi.")
                ->withCookie($cookie);
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Web update cart item quantity.
     */
    public function updateCartItem(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $user = $request->user();
        $sessionId = $this->cartSessionId($request);

        try {
            $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
            $item = $this->cartService->updateItem($cart, $id, (float) $validated['quantity']);
            $quote = $this->cartService->getCartQuote($cart);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Savatcha yangilandi.',
                    'data' => ['cart_item_id' => $item->id, 'quote' => $quote]
                ]);
            }

            return redirect()->route('cart')->with('success', 'Savatcha yangilandi.');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Web remove cart item.
     */
    public function removeCartItem(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $sessionId = $this->cartSessionId($request);

        try {
            $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
            $this->cartService->removeItem($cart, $id);
            $quote = $this->cartService->getCartQuote($cart);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Mahsulot savatchadan o'chirildi.",
                    'data' => ['quote' => $quote]
                ]);
            }

            return redirect()->route('cart')->with('success', "Mahsulot savatchadan o'chirildi.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Checkout page.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->guest(route('login'))
                ->with('info', "Buyurtmani rasmiylashtirish uchun avval tizimga kiring yoki ro'yxatdan o'ting. Savatchangiz to'liq saqlanadi.");
        }

        $sessionId = $this->cartSessionId($request);

        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
        $quote = $this->cartService->getCartQuote($cart);

        if ($quote['is_empty'] || empty($quote['sellers'])) {
            return redirect()->route('cart')->with('error', "Savatchangiz bo'sh. Buyurtma berish uchun mahsulot tanlang.");
        }

        $idempotencyKey = (string) Str::uuid();

        return view('storefront.checkout', compact('cart', 'quote', 'idempotencyKey', 'user'));
    }

    /**
     * Process web checkout.
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'region' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'address_line' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|in:cash_on_delivery,click,payme,uzum,nasiya,demo',
            'nasiya_months' => 'nullable|integer|in:3,6,12',
            'promocode' => 'nullable|string|max:32',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        if (!$user) {
            return redirect()->guest(route('login'))
                ->with('info', "Buyurtmani yakunlash uchun tizimga kiring yoki ro'yxatdan o'ting. Savatchangiz saqlanadi.");
        }

        $sessionId = $this->cartSessionId($request);
        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);

        $paymentMethod = $validated['payment_method'] ?? 'cash_on_delivery';

        $payload = [
            'shipping_address' => [
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'region' => $validated['region'],
                'city' => $validated['city'],
                'address_line' => $validated['address_line'],
            ],
            'notes' => $validated['notes'] ?? null,
            'payment_method' => $paymentMethod,
            'nasiya_months' => $validated['nasiya_months'] ?? 6,
            'promocode' => $validated['promocode'] ?? null,
        ];

        try {
            $result = $this->checkoutService->processCheckout(
                $user,
                $payload,
                $validated['idempotency_key'] ?? (string) Str::uuid()
            );

            $order = $result['order'];

            // Remember orders placed in this browser so a guest can open their own confirmation / payment pages
            $request->session()->push('placed_orders', $order->order_number);

            // If customer picked online payment (Click / Payme), take them directly to payment checkout
            if (in_array($paymentMethod, ['click', 'payme', 'uzum'])) {
                return redirect()->route('payment.checkout', ['orderNumber' => $order->order_number]);
            }

            if ($paymentMethod === 'nasiya') {
                $nasiyaMonths = (int)($payload['nasiya_months'] ?? 6);
                return redirect()->route('order.success', ['orderNumber' => $order->order_number])
                    ->with('success', "Buyurtmangiz muddatli to'lov (Nasiya - {$nasiyaMonths} oyga) asosida muvaffaqiyatli qabul qilindi! Boshlang'ich to'lov: 0 UZS.");
            }

            return redirect()->route('order.success', ['orderNumber' => $order->order_number])
                ->with('success', "Buyurtmangiz muvaffaqiyatli qabul qilindi! To'lov eshikka yetkazib berilganda naqd yoki karta orqali qabul qilinadi.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Order confirmation and success view.
     */
    public function orderSuccess(Request $request, string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['suborders.items', 'suborders.seller', 'suborders.shipments', 'payments'])
            ->firstOrFail();

        abort_unless(self::canAccessOrder($request, $order), 404);

        return view('storefront.order_success', compact('order'));
    }

    /**
     * Live GPS tracking view with interactive Leaflet map.
     */
    public function tracking(Request $request, ?string $trackingNumber = null)
    {
        $query = Shipment::with([
            'suborder.order.user',
            'suborder.seller',
            'suborder.items',
            'courier',
            'courierLocations' => fn($q) => $q->orderBy('recorded_at', 'asc'),
            'events' => fn($q) => $q->orderBy('created_at', 'desc'),
        ]);

        $user = $request->user();

        if ($trackingNumber) {
            $shipment = (clone $query)->where('tracking_number', $trackingNumber)->first();
        } else {
            // Without a number, only the signed-in customer's own latest parcel is shown — never a stranger's
            $shipment = $user
                ? (clone $query)->whereHas('suborder.order', fn($q) => $q->where('user_id', $user->id))->latest()->first()
                : null;
        }

        if (!$shipment) {
            return view('storefront.tracking_empty', ['trackingNumber' => $trackingNumber]);
        }

        // Anyone with a tracking number sees progress; personal details only for the order's owner / staff
        $order = $shipment->suborder?->order;
        $canSeeDetails = ($order && self::canAccessOrder($request, $order))
            || ($user && $shipment->courier_id === $user->id);

        // Warehouse / Origin
        $warehouse = [
            'name' => $shipment->pickup_address['title'] ?? ($shipment->suborder?->seller?->name . ' Ombori'),
            'address' => $shipment->pickup_address['address'] ?? 'Toshkent sh., Sergeli sanoat zonasi, 14-bino',
            'lat' => (float) ($shipment->pickup_address['latitude'] ?? 41.2285),
            'lng' => (float) ($shipment->pickup_address['longitude'] ?? 69.2155),
        ];

        // Customer / Destination
        $destination = [
            'recipient' => $shipment->delivery_address['recipient'] ?? ($shipment->suborder?->order?->shipping_address['recipient_name'] ?? 'Mijoz'),
            'phone' => $shipment->delivery_address['phone'] ?? ($shipment->suborder?->order?->shipping_address['phone'] ?? '+998 90 987 65 43'),
            'address' => $shipment->delivery_address['address'] ?? ($shipment->suborder?->order?->shipping_address['address_line'] ?? 'Mustaqillik shoh ko\'chasi, 105-uy'),
            'lat' => (float) ($shipment->delivery_address['latitude'] ?? ($shipment->suborder?->order?->shipping_address['latitude'] ?? 41.3111)),
            'lng' => (float) ($shipment->delivery_address['longitude'] ?? ($shipment->suborder?->order?->shipping_address['longitude'] ?? 69.2797)),
        ];

        if (!$canSeeDetails) {
            $destination['recipient'] = Str::limit($destination['recipient'], 1, '***');
            $destination['phone'] = preg_replace('/\d(?=\d{2})/', '*', (string) $destination['phone']);
            $destination['address'] = $shipment->delivery_address['city']
                ?? $order?->shipping_address['city']
                ?? 'Manzil yashirilgan';
        }

        // Courier locations path
        $locations = $shipment->courierLocations->map(fn($loc) => [
            'id' => $loc->id,
            'lat' => (float) $loc->latitude,
            'lng' => (float) $loc->longitude,
            'speed' => (float) ($loc->speed ?? 0),
            'heading' => (float) ($loc->heading ?? 0),
            'recorded_at' => $loc->recorded_at?->format('H:i') ?? '',
            'recorded_iso' => $loc->recorded_at?->toIso8601String() ?? '',
        ])->values()->all();

        // Current / Latest location
        $latest = !empty($locations) ? end($locations) : [
            'lat' => 41.2995,
            'lng' => 69.2725,
            'speed' => 35,
            'heading' => 20,
            'recorded_at' => now()->format('H:i'),
        ];

        // Only order owner or admin sees OTP (Courier must NOT see it)
        $deliveryOtp = null;
        if ($order && self::canAccessOrder($request, $order) && (!$user || !$user->hasRole('courier') || $order->user_id === $user->id)) {
            $deliveryOtp = $shipment->delivery_otp ?: $shipment->ensureDeliveryOtp();
        }

        // Return JSON for client polling
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'delivery_otp' => $deliveryOtp,
                'courier' => [
                    'name' => $shipment->courier?->name ?? 'Kuryer hali tayinlanmagan',
                    'phone' => $canSeeDetails ? $shipment->courier?->phone : null,
                    'vehicle' => null,
                    'plate' => null,
                ],
                'warehouse' => $warehouse,
                'destination' => $destination,
                'latest' => $latest,
                'locations' => $locations,
                'eta' => '10-15 daqiqa',
            ]);
        }

        // "Other parcels" list: only the viewer's own shipments
        $allShipments = $user
            ? Shipment::select('shipments.tracking_number', 'shipments.status', 'shipments.created_at')
                ->whereHas('suborder.order', fn($q) => $q->where('user_id', $user->id))
                ->orderBy('shipments.id', 'desc')
                ->take(8)
                ->get()
            : collect();

        return view('storefront.tracking', compact('shipment', 'warehouse', 'destination', 'locations', 'latest', 'allShipments', 'canSeeDetails', 'deliveryOtp'));
    }

    /**
     * Guest carts are keyed by a long-lived cookie so they survive session expiry and the
     * session-id regeneration that happens on login (which is when the guest cart gets merged).
     */
    protected function cartSessionId(Request $request): string
    {
        return (string) ($request->cookie('cart_session_id') ?: $request->session()->getId());
    }

    /**
     * Whether the current visitor may see an order's details: its buyer, a guest who placed it in
     * this browser session, staff, the courier carrying it, or a seller fulfilling part of it.
     */
    public static function canAccessOrder(Request $request, Order $order): bool
    {
        if (in_array($order->order_number, (array) $request->session()->get('placed_orders', []), true)) {
            return true;
        }

        $user = $request->user();
        if (!$user) {
            return false;
        }

        if ($order->user_id === $user->id || $user->hasRole(['superadmin', 'admin', 'moderator', 'logistics_operator'])) {
            return true;
        }

        $order->loadMissing('suborders.shipments');

        if ($order->suborders->flatMap->shipments->contains('courier_id', $user->id)) {
            return true;
        }

        return $order->suborders->contains(
            fn($sub) => $user->seller_id === $sub->seller_id || $user->belongsToSeller($sub->seller_id)
        );
    }

    /**
     * Check and validate promocode on checkout via AJAX.
     */
    public function checkPromocode(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string)$request->input('code', '')));
        $subtotal = (int)$request->input('subtotal', 0);

        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Promokod kiritilmadi.'], 422);
        }

        $promo = \App\Models\Promocode::where('code', $code)->first();
        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Bunday promokod mavjud emas.'], 404);
        }

        $res = $promo->validateForSubtotal($subtotal);
        if (!$res['valid']) {
            return response()->json(['success' => false, 'message' => $res['message']], 422);
        }

        $discount = $promo->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Promokod muvaffaqiyatli qo\'llandi!',
            'data' => [
                'code' => $promo->code,
                'discount' => $discount,
                'discount_formatted' => number_format($discount, 0, '.', ' ') . ' UZS',
            ]
        ]);
    }
}
