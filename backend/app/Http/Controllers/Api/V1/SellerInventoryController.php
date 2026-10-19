<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Warehouse;
use App\Services\Audit\AuditService;
use App\Services\Inventory\InventoryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SellerInventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

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
     * List current seller's warehouses.
     */
    public function warehouses(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        $warehouses = Warehouse::where('seller_id', $seller->id)
            ->withCount('balances')
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success($warehouses);
    }

    /**
     * Create a new warehouse for current seller.
     */
    public function storeWarehouse(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        AuditService::log('warehouse.created', $warehouse, null, $warehouse->toArray());

        return ApiResponse::created($warehouse, "Ombor muvaffaqiyatli yaratildi");
    }

    /**
     * View specific warehouse (multi-tenant isolated).
     */
    public function showWarehouse(Request $request, int $id): JsonResponse
    {
        $warehouse = Warehouse::with(['balances.variant.product'])->findOrFail($id);

        if ($request->user()->cannot('view', $warehouse)) {
            throw new AccessDeniedHttpException("Ushbu ombor sizning do'koningizga tegishli emas");
        }

        return ApiResponse::success($warehouse);
    }

    /**
     * Update warehouse (multi-tenant isolated).
     */
    public function updateWarehouse(Request $request, int $id): JsonResponse
    {
        $warehouse = Warehouse::findOrFail($id);

        if ($request->user()->cannot('update', $warehouse)) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining omborini o'zgartira olmaysiz");
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $warehouse->only(array_keys($validated));
        $warehouse->update($validated);

        AuditService::log('warehouse.updated', $warehouse, $oldValues, $validated);

        return ApiResponse::success($warehouse, "Ombor ma'lumotlari yangilandi");
    }

    /**
     * Adjust physical inventory stock with movement history (multi-tenant isolated).
     */
    public function adjustStock(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity_delta' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', 'string', Rule::in(['inflow', 'outflow', 'adjustment'])],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $variant = ProductVariant::with('product')->findOrFail($validated['product_variant_id']);

        // Strict multi-tenant isolation: Warehouse must belong to current seller
        if ($warehouse->seller_id !== $seller->id) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining omboridagi qoldiqni o'zgartira olmaysiz");
        }

        // Strict multi-tenant isolation: Product variant must belong to current seller
        if ($variant->product->seller_id !== $seller->id) {
            throw new AccessDeniedHttpException("Siz boshqa sotuvchining mahsuloti zaxirasini o'zgartira olmaysiz");
        }

        try {
            $balance = $this->inventoryService->adjustStock(
                warehouse: $warehouse,
                variant: $variant,
                quantityDelta: $validated['quantity_delta'],
                type: $validated['type'],
                reason: $validated['reason'],
                actor: $request->user()
            );

            return ApiResponse::success($balance->load('warehouse:id,name', 'variant.product:id,name'), "Ombor zaxirasi muvaffaqiyatli yangilandi");
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 'INVALID_INVENTORY_ADJUSTMENT',
                status: 422
            );
        }
    }

    /**
     * View inventory movements audit trail for current seller's warehouses.
     */
    public function movements(Request $request): JsonResponse
    {
        $seller = $this->resolveSeller($request);

        $warehouseIds = Warehouse::where('seller_id', $seller->id)->pluck('id');

        $movements = InventoryMovement::whereIn('warehouse_id', $warehouseIds)
            ->with(['warehouse:id,name', 'variant.product:id,name', 'user:id,name'])
            ->orderByDesc('id')
            ->paginate(20);

        return ApiResponse::success($movements);
    }
}
