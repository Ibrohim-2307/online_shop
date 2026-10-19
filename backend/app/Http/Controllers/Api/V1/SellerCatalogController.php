<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Services\Audit\AuditService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SellerCatalogController extends Controller
{
    /**
     * Resolve current authenticated seller tenant.
     */
    protected function resolveSeller(Request $request): Seller
    {
        $user = $request->user();
        $seller = $request->attributes->get('current_seller') ?? $user->activeSeller();

        if (!$seller) {
            throw new AccessDeniedHttpException("Siz biron bir do'konga a'zo emassiz");
        }

        if ($seller->status === 'suspended' && !$user->hasRole('admin')) {
            throw new AccessDeniedHttpException("Ushbu do'kon faoliyati to'xtatilgan (suspended). Boshqaruv amallari cheklangan.");
        }

        return $seller;
    }

    /**
     * List current seller's products.
     */
    public function index(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        $products = Product::where('seller_id', $seller->id)
            ->with(['category:id,name', 'variants.priceTiers'])
            ->orderByDesc('id')
            ->paginate(20);

        return ApiResponse::success($products);
    }

    /**
     * Create a new product for current seller.
     */
    public function store(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'trade_mode' => ['required', 'string', Rule::in(['retail_only', 'wholesale_only', 'both'])],
            'unit' => ['required', 'string', Rule::in(['piece', 'box', 'kg', 'ton'])],
            'items_per_box' => ['required_if:unit,box', 'nullable', 'integer', 'min:1'],
            'unit_step' => ['nullable', 'numeric', 'gt:0'],
            'min_order_quantity' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $slug = Str::slug($validated['name']) . '-' . Str::random(6);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'trade_mode' => $validated['trade_mode'],
            'unit' => $validated['unit'],
            'items_per_box' => $validated['items_per_box'] ?? null,
            'unit_step' => $validated['unit_step'] ?? 1.000,
            'min_order_quantity' => $validated['min_order_quantity'] ?? 1.000,
            'status' => 'pending',
            'is_active' => true,
        ]);

        AuditService::log('product.created', $product, null, $product->toArray());

        return ApiResponse::created($product, "Mahsulot muvaffaqiyatli yaratildi");
    }

    /**
     * View specific product (multi-tenant isolated).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $product = Product::with(['variants.priceTiers', 'variants.inventoryBalances.warehouse'])->findOrFail($id);

        if ($request->user()->cannot('view', $product)) {
            throw new AccessDeniedHttpException("Ushbu mahsulot sizning do'koningizga tegishli emas");
        }

        return ApiResponse::success($product);
    }

    /**
     * Update specific product (multi-tenant isolated).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if ($request->user()->cannot('update', $product)) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining mahsulotini o'zgartira olmaysiz");
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'trade_mode' => ['nullable', 'string', Rule::in(['retail_only', 'wholesale_only', 'both'])],
            'unit' => ['nullable', 'string', Rule::in(['piece', 'box', 'kg', 'ton'])],
            'items_per_box' => ['nullable', 'integer', 'min:1'],
            'unit_step' => ['nullable', 'numeric', 'gt:0'],
            'min_order_quantity' => ['nullable', 'numeric', 'gt:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $product->only(array_keys($validated));
        $product->update($validated);

        AuditService::log('product.updated', $product, $oldValues, $validated);

        return ApiResponse::success($product, "Mahsulot ma'lumotlari yangilandi");
    }

    /**
     * Delete product (multi-tenant isolated).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if ($request->user()->cannot('delete', $product)) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining mahsulotini o'chira olmaysiz");
        }

        $productData = ['id' => $product->id, 'name' => $product->name];
        $product->delete();

        AuditService::log('product.deleted', null, $productData, null);

        return ApiResponse::success(null, "Mahsulot muvaffaqiyatli o'chirildi");
    }

    /**
     * Add SKU variant to product (multi-tenant isolated).
     */
    public function storeVariant(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        if ($request->user()->cannot('update', $product)) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining mahsulotiga variant qo'sha olmaysiz");
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'name' => ['required', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'barcode' => ['nullable', 'string', 'max:100'],
        ]);

        $variant = $product->variants()->create($validated);

        AuditService::log('product_variant.created', $variant, null, $variant->toArray());

        return ApiResponse::created($variant, "Mahsulot varianti muvaffaqiyatli qo'shildi");
    }

    /**
     * Set / replace price tiers for a variant (multi-tenant isolated).
     */
    public function setPriceTiers(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        if ($request->user()->cannot('update', $variant->product)) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining mahsulot narxlarini o'zgartira olmaysiz");
        }

        $validated = $request->validate([
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.min_quantity' => ['required', 'numeric', 'gt:0'],
            'tiers.*.max_quantity' => ['nullable', 'numeric', 'gt:0'],
            'tiers.*.unit_price' => ['required', 'integer', 'gt:0'], // Integer / minor unit without float
            'tiers.*.currency' => ['nullable', 'string', 'size:3'],
        ]);

        // Remove old tiers and insert new ones atomically
        $variant->priceTiers()->delete();

        $createdTiers = [];
        foreach ($validated['tiers'] as $tierData) {
            $createdTiers[] = $variant->priceTiers()->create([
                'min_quantity' => $tierData['min_quantity'],
                'max_quantity' => $tierData['max_quantity'] ?? null,
                'unit_price' => $tierData['unit_price'],
                'currency' => $tierData['currency'] ?? 'UZS',
            ]);
        }

        AuditService::log('price_tiers.updated', $variant, null, ['tiers_count' => count($createdTiers)]);

        return ApiResponse::success($createdTiers, "Narx pog'onalari muvaffaqiyatli yangilandi");
    }
}
