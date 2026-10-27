<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Rfq;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\Suborder;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    /**
     * Display the Seller Dashboard web page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['seller_owner', 'seller_manager', 'seller_operator'])) {
            return redirect()->route('login')->with('error', 'Sotuvchi paneliga kirish uchun do\'kon xodimi yoki egasi hisobi talab qilinadi.');
        }

        $sellerId = $user->seller_id;
        $seller = $sellerId ? Seller::find($sellerId) : null;
        if (!$seller) {
            // Fallback for first demo seller if membership query differs
            $seller = Seller::first();
        }

        if (!$seller) {
            return redirect()->route('home')->with('error', "Sizga biriktirilgan faol do'kon topilmadi.");
        }

        $products = Product::with(['variants.priceTiers', 'variants.inventoryBalances.warehouse'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        $suborders = Suborder::with(['order', 'items', 'shipments'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->limit(15)
            ->get();

        $procurementOrders = Order::where('user_id', $user->id)
            ->with(['suborders.seller', 'suborders.items', 'suborders.shipments'])
            ->latest()
            ->limit(15)
            ->get();

        $warehouses = Warehouse::with('balances.variant')
            ->where('seller_id', $seller->id)
            ->get();

        $rfqs = Rfq::with('quotations')
            ->latest()
            ->limit(10)
            ->get();

        // Calculate ledger balance: credits - debits
        $ledgerEntries = SellerLedger::where('seller_id', $seller->id)->latest()->limit(15)->get();
        $totalCredit = SellerLedger::where('seller_id', $seller->id)->where('type', 'credit')->sum('amount');
        $totalDebit = SellerLedger::where('seller_id', $seller->id)->where('type', 'debit')->sum('amount');
        $currentBalance = $totalCredit - $totalDebit;

        $stats = [
            'total_products' => $products->count(),
            'total_suborders' => Suborder::where('seller_id', $seller->id)->count(),
            'pending_orders' => Suborder::where('seller_id', $seller->id)->where('status', 'pending')->count(),
            'procurement_orders_count' => $procurementOrders->count(),
            'in_transit_procurement' => $procurementOrders->filter(fn($o) => $o->suborders->flatMap->shipments->contains(fn($s) => $s->status === 'in_transit'))->count(),
            'current_balance' => $currentBalance,
            'rating' => $seller->rating,
        ];

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('seller.dashboard', compact('seller', 'products', 'categories', 'suborders', 'procurementOrders', 'warehouses', 'rfqs', 'ledgerEntries', 'stats'));
    }

    /**
     * Store a newly created product in seller catalog.
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['seller_owner', 'seller_manager', 'seller_operator'])) {
            abort(403, 'Faqat do\'kon xodimlari mahsulot qo\'shishi mumkin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'trade_mode' => ['required', 'in:wholesale,retail,both'],
            'unit' => ['required', 'in:piece,box,kg,ton'],
            'min_order_quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_step' => ['required', 'numeric', 'min:0.001'],
            'items_per_box' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:product_variants,sku'],
            'retail_price' => ['required', 'integer', 'min:100'],
            'tier_300_price' => ['nullable', 'integer', 'min:100'],
            'tier_1000_price' => ['nullable', 'integer', 'min:100'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
        ]);

        $seller = Seller::find($user->seller_id) ?: Seller::first();
        if (!$seller) {
            return back()->with('error', 'Sotuvchi do\'koni topilmadi.');
        }

        $warehouseId = $request->warehouse_id;
        if (!$warehouseId) {
            $warehouse = Warehouse::firstOrCreate(
                ['seller_id' => $seller->id],
                ['name' => 'Asosiy Ombor', 'code' => 'WH-' . $seller->id . '-01', 'address' => 'Toshkent sh.', 'is_active' => true]
            );
            $warehouseId = $warehouse->id;
        }

        $slug = Str::slug($validated['name']);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(5));
        }

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
            } elseif (str_contains($catSlug, 'ichimlik') || str_contains($catSlug, 'suv')) {
                $imageUrl = '/images/products/suv.jpg';
            } elseif (str_contains($catSlug, 'metall') || str_contains($catSlug, 'armatura')) {
                $imageUrl = '/images/products/armatura.jpg';
            } elseif (str_contains($catSlug, 'yogoch') || str_contains($catSlug, 'fanera')) {
                $imageUrl = '/images/products/profil_truba.jpg';
            }
        }

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_url' => $imageUrl,
            'trade_mode' => $validated['trade_mode'],
            'unit' => $validated['unit'],
            'items_per_box' => $validated['items_per_box'] ?? 1,
            'unit_step' => $validated['unit_step'],
            'min_order_quantity' => $validated['min_order_quantity'],
            'status' => 'approved',
            'is_active' => true,
        ]);

        $sku = !empty($validated['sku']) ? $validated['sku'] : ('SKU-' . strtoupper(Str::random(8)));
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'name' => 'Standart',
            'is_active' => true,
        ]);

        $retailPrice = (int) $validated['retail_price'];
        $tier300Price = !empty($validated['tier_300_price']) ? (int) $validated['tier_300_price'] : (int) round($retailPrice * 0.92);
        $tier1000Price = !empty($validated['tier_1000_price']) ? (int) $validated['tier_1000_price'] : (int) round($retailPrice * 0.85);

        // Tier 1: 1 - 299
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => (float) $validated['min_order_quantity'],
            'max_quantity' => 299.0,
            'unit_price' => $retailPrice,
            'currency' => 'UZS',
        ]);

        // Tier 2: 300 - 999
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 300.0,
            'max_quantity' => 999.0,
            'unit_price' => $tier300Price,
            'currency' => 'UZS',
        ]);

        // Tier 3: 1000+
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1000.0,
            'max_quantity' => null,
            'unit_price' => $tier1000Price,
            'currency' => 'UZS',
        ]);

        $stock = (float) $validated['stock_quantity'];
        InventoryBalance::create([
            'warehouse_id' => $warehouseId,
            'product_variant_id' => $variant->id,
            'quantity' => $stock,
            'reserved_quantity' => 0.0,
        ]);

        if ($stock > 0) {
            InventoryMovement::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $variant->id,
                'type' => 'inflow',
                'quantity' => $stock,
                'balance_before' => 0.0,
                'balance_after' => $stock,
                'reason' => 'Yangi tovar qo\'shildi va boshlang\'ich zaxira kiritildi',
                'user_id' => $user->id,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', "Yangi mahsulot '{$product->name}' muvaffaqiyatli saqlandi va katalogga qo'shildi!");
    }
}
