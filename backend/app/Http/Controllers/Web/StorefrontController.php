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
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
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
            ->orderBy('sort_order')
            ->take(8)
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
        $quote = $this->cartService->getCartQuote($cart);

        return view('storefront.cart', compact('cart', 'quote'));
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

        $payload = [
            'shipping_address' => [
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'region' => $validated['region'],
                'city' => $validated['city'],
                'address_line' => $validated['address_line'],
            ],
            'notes' => $validated['notes'] ?? null,
        ];

        try {
            $result = $this->checkoutService->processCheckout(
                $user,
                $payload,
                $validated['idempotency_key'] ?? (string) Str::uuid()
            );

            $order = $result['order'];

            return redirect()->route('order.success', ['orderNumber' => $order->order_number]);
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
            ->with(['suborders.items', 'suborders.seller', 'suborders.shipments'])
            ->firstOrFail();

        return view('storefront.order_success', compact('order'));
    }
}
