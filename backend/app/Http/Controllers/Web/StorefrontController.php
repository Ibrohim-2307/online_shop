<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Address;
use App\Models\Cart;
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
            ->with(['seller:id,name,slug,rating', 'category:id,name,slug', 'variants.priceTiers'])
            ->latest()
            ->take(8)
            ->get();

        $wholesaleProducts = Product::where('status', 'approved')
            ->where('is_active', true)
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
        $query = Product::where('status', 'approved')->where('is_active', true);

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

        // Sort
        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderBy('id', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('id', 'desc');
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
     * Product details page with variant switching, all-units tiered price calculation, and stock.
     */
    public function product(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->with([
                'seller:id,name,slug,rating,phone,email,address',
                'category:id,name,slug',
                'variants.priceTiers',
                'variants.inventoryBalances.warehouse:id,name,address',
            ])
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->with(['seller:id,name,slug,rating', 'variants.priceTiers'])
            ->take(4)
            ->get();

        return view('storefront.product', compact('product', 'relatedProducts'));
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
        $sessionId = $request->session()->getId();

        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);

        // If current cart is empty, check for recently created orphan guest cart items and adopt them
        if ($cart->items->isEmpty()) {
            $orphan = Cart::where('status', 'active')
                ->where('id', '!=', $cart->id)
                ->whereNull('user_id')
                ->whereHas('items')
                ->where('created_at', '>=', now()->subHours(2))
                ->latest()
                ->first();

            if ($orphan) {
                foreach ($orphan->items as $it) {
                    $this->cartService->addItem($cart, $it->product_variant_id, (float) $it->quantity);
                }
                $orphan->items()->delete();
                $orphan->delete();
                $cart->refresh();
            }
        }

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
        $sessionId = $request->session()->getId();

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
        $sessionId = $request->session()->getId();

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
        $sessionId = $request->session()->getId();

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
        $sessionId = $request->session()->getId();

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
            'payment_method' => 'nullable|string|in:cash_on_delivery,click,payme,uzum,demo',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        if (!$user) {
            // Find or create guest customer user
            $phone = preg_replace('/[^\d+]/', '', $validated['phone']);
            $user = \App\Models\User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $validated['recipient_name'],
                    'email' => 'guest_' . uniqid() . '@example.uz',
                    'password' => bcrypt(Str::random(16)),
                    'is_active' => true,
                ]
            );
        }

        $sessionId = $request->session()->getId();
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
        ];

        try {
            $result = $this->checkoutService->processCheckout(
                $user,
                $payload,
                $validated['idempotency_key'] ?? (string) Str::uuid()
            );

            $order = $result['order'];

            // If customer picked online payment (Click / Payme), take them directly to payment checkout
            if (in_array($paymentMethod, ['click', 'payme', 'uzum'])) {
                return redirect()->route('payment.checkout', ['orderNumber' => $order->order_number]);
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
    public function orderSuccess(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['suborders.items', 'suborders.seller', 'suborders.shipments', 'payments'])
            ->firstOrFail();

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

        if ($trackingNumber) {
            $shipment = $query->where('tracking_number', $trackingNumber)->first();
        } else {
            $shipment = $query->where('tracking_number', 'TRK-UZ-778899')->first()
                     ?? $query->where('status', 'in_transit')->latest()->first()
                     ?? $query->latest()->first();
        }

        if (!$shipment) {
            return view('storefront.tracking_empty', ['trackingNumber' => $trackingNumber]);
        }

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

        // Return JSON for client polling
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'courier' => [
                    'name' => $shipment->courier?->name ?? 'Rustam Kuryer',
                    'phone' => '+998 97 123 45 67',
                    'vehicle' => 'Labo / Damas (Kamyon)',
                    'plate' => '01 A 777 AA',
                ],
                'warehouse' => $warehouse,
                'destination' => $destination,
                'latest' => $latest,
                'locations' => $locations,
                'eta' => '10-15 daqiqa',
            ]);
        }

        $allShipments = Shipment::select('tracking_number', 'status', 'created_at')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        return view('storefront.tracking', compact('shipment', 'warehouse', 'destination', 'locations', 'latest', 'allShipments'));
    }
}
