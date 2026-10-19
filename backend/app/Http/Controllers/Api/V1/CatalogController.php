<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Pricing\PricingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CatalogController extends Controller
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    /**
     * List category tree.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success($categories);
    }

    /**
     * List active and approved products with filtering and pagination.
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::where('status', 'approved')
            ->where('is_active', true)
            ->with([
                'seller:id,name,slug,rating',
                'category:id,name,slug',
                'variants.priceTiers',
            ])
            ->orderByDesc('id');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($tradeMode = $request->get('trade_mode')) {
            $query->where('trade_mode', $tradeMode);
        }

        if ($sellerId = $request->get('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        $products = $query->paginate(20);

        return ApiResponse::success($products);
    }

    /**
     * View product details by slug or ID.
     */
    public function show(string $slugOrId): JsonResponse
    {
        $product = Product::where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($slugOrId) {
                if (is_numeric($slugOrId)) {
                    $q->where('id', $slugOrId)->orWhere('slug', $slugOrId);
                } else {
                    $q->where('slug', $slugOrId);
                }
            })
            ->with([
                'seller:id,name,slug,rating,phone',
                'category:id,name,slug',
                'variants.priceTiers',
                'variants.inventoryBalances.warehouse:id,name',
            ])
            ->first();

        if (!$product) {
            throw new NotFoundHttpException("Mahsulot topilmadi");
        }

        return ApiResponse::success($product);
    }

    /**
     * Calculate price quote for a variant and quantity without floating-point math.
     */
    public function calculateQuote(Request $request, int $variantId): JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $variant = ProductVariant::with(['product', 'priceTiers'])->findOrFail($variantId);

        try {
            $quote = $this->pricingService->calculateQuote($variant, $request->input('quantity'));
            return ApiResponse::success($quote);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 'INVALID_QUOTE_REQUEST',
                status: 422
            );
        }
    }
}
