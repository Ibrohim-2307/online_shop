<?php

namespace App\Services\Inventory;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Adjust physical inventory balance and record immutable inventory movement audit entry.
     */
    public function adjustStock(
        Warehouse $warehouse,
        ProductVariant $variant,
        string|int|float $quantityDelta,
        string $type,
        string $reason,
        ?User $actor = null,
        ?string $refType = null,
        ?int $refId = null
    ): InventoryBalance {
        $deltaStr = (string) $quantityDelta;

        return DB::transaction(function () use (
            $warehouse,
            $variant,
            $deltaStr,
            $type,
            $reason,
            $actor,
            $refType,
            $refId
        ) {
            // Lock inventory row for update to prevent race conditions
            $balance = InventoryBalance::where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = InventoryBalance::create([
                    'warehouse_id' => $warehouse->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => '0.000',
                    'reserved_quantity' => '0.000',
                ]);
            }

            $before = (string) $balance->quantity;
            $after = bcadd($before, $deltaStr, 3);

            // Cannot have negative physical balance
            if (bccomp($after, '0.000', 3) < 0) {
                throw new InvalidArgumentException(
                    "Omborda yetarli zaxira mavjud emas. Joriy qoldiq: {$before}, so'ralgan o'zgarish: {$deltaStr}"
                );
            }

            $balance->update(['quantity' => $after]);

            // Record movement in history
            InventoryMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => abs((float) $deltaStr),
                'balance_before' => $before,
                'balance_after' => $after,
                'reason' => $reason,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'user_id' => $actor?->id,
                'created_at' => now(),
            ]);

            return $balance;
        });
    }

    /**
     * Temporarily reserve stock for checkout quotes / pending orders.
     */
    public function reserveStock(
        Warehouse $warehouse,
        ProductVariant $variant,
        string|int|float $quantity,
        string $reason = 'Checkout reservation',
        ?User $actor = null,
        ?string $refType = null,
        ?int $refId = null
    ): InventoryBalance {
        $qtyStr = (string) $quantity;

        return DB::transaction(function () use (
            $warehouse,
            $variant,
            $qtyStr,
            $reason,
            $actor,
            $refType,
            $refId
        ) {
            $balance = InventoryBalance::where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $available = bcsub((string) $balance->quantity, (string) $balance->reserved_quantity, 3);

            if (bccomp($available, $qtyStr, 3) < 0) {
                throw new InvalidArgumentException(
                    "Zaxiralash uchun yetarli erkin qoldiq mavjud emas. Erkin qoldiq: {$available}, so'ralgan: {$qtyStr}"
                );
            }

            $before = (string) $balance->reserved_quantity;
            $after = bcadd($before, $qtyStr, 3);

            $balance->update(['reserved_quantity' => $after]);

            InventoryMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'type' => 'reservation',
                'quantity' => $qtyStr,
                'balance_before' => $before,
                'balance_after' => $after,
                'reason' => $reason,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'user_id' => $actor?->id,
                'created_at' => now(),
            ]);

            return $balance;
        });
    }

    /**
     * Release previously reserved stock upon expiry or cancellation.
     */
    public function releaseStock(
        Warehouse $warehouse,
        ProductVariant $variant,
        string|int|float $quantity,
        string $reason = 'Reservation expired or cancelled',
        ?User $actor = null,
        ?string $refType = null,
        ?int $refId = null
    ): InventoryBalance {
        $qtyStr = (string) $quantity;

        return DB::transaction(function () use (
            $warehouse,
            $variant,
            $qtyStr,
            $reason,
            $actor,
            $refType,
            $refId
        ) {
            $balance = InventoryBalance::where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = (string) $balance->reserved_quantity;
            $after = bcsub($before, $qtyStr, 3);

            if (bccomp($after, '0.000', 3) < 0) {
                $after = '0.000';
            }

            $balance->update(['reserved_quantity' => $after]);

            InventoryMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'type' => 'release',
                'quantity' => $qtyStr,
                'balance_before' => $before,
                'balance_after' => $after,
                'reason' => $reason,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'user_id' => $actor?->id,
                'created_at' => now(),
            ]);

            return $balance;
        });
    }
}
