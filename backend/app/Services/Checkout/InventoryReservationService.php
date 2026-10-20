<?php

namespace App\Services\Checkout;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryReservationService
{
    /**
     * Atomically reserve stock across active warehouses for a variant with row locking and TTL.
     */
    public function reserveVariantStock(
        ProductVariant $variant,
        float|string $quantity,
        ?Order $order = null,
        int $ttlMinutes = 15
    ): InventoryReservation {
        $qtyStr = (string) $quantity;

        return DB::transaction(function () use ($variant, $qtyStr, $order, $ttlMinutes) {
            // Find an active warehouse for the product's seller that has sufficient available balance
            $sellerId = $variant->product->seller_id;

            $balances = InventoryBalance::join('warehouses', 'inventory_balances.warehouse_id', '=', 'warehouses.id')
                ->where('warehouses.seller_id', $sellerId)
                ->where('warehouses.is_active', true)
                ->where('inventory_balances.product_variant_id', $variant->id)
                ->select('inventory_balances.*')
                ->lockForUpdate()
                ->get();

            $chosenBalance = null;
            foreach ($balances as $b) {
                $available = bcsub((string) $b->quantity, (string) $b->reserved_quantity, 3);
                if (bccomp($available, $qtyStr, 3) >= 0) {
                    $chosenBalance = $b;
                    break;
                }
            }

            if (!$chosenBalance) {
                throw new InvalidArgumentException(
                    "Omborda so'ralgan miqdorda yetarli erkin zaxira mavjud emas ({$variant->sku}, miqdor: {$qtyStr})."
                );
            }

            $beforeReserved = (string) $chosenBalance->reserved_quantity;
            $afterReserved = bcadd($beforeReserved, $qtyStr, 3);
            $chosenBalance->update(['reserved_quantity' => $afterReserved]);

            // Create reservation record
            $reservation = InventoryReservation::create([
                'order_id' => $order?->id,
                'product_variant_id' => $variant->id,
                'warehouse_id' => $chosenBalance->warehouse_id,
                'quantity' => $qtyStr,
                'status' => 'reserved',
                'expires_at' => now()->addMinutes($ttlMinutes),
            ]);

            // Record movement audit
            InventoryMovement::create([
                'warehouse_id' => $chosenBalance->warehouse_id,
                'product_variant_id' => $variant->id,
                'type' => 'reservation',
                'quantity' => $qtyStr,
                'balance_before' => $beforeReserved,
                'balance_after' => $afterReserved,
                'reason' => "Order reservation #{$reservation->id}" . ($order ? " for Order #{$order->order_number}" : ""),
                'reference_type' => 'InventoryReservation',
                'reference_id' => $reservation->id,
                'user_id' => $order?->user_id,
                'created_at' => now(),
            ]);

            return $reservation;
        });
    }

    /**
     * Release a specific active reservation back to available stock.
     */
    public function releaseReservation(InventoryReservation $reservation, string $reason = 'Manual release'): void
    {
        DB::transaction(function () use ($reservation, $reason) {
            // Lock reservation row
            $lockedRes = InventoryReservation::where('id', $reservation->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedRes || $lockedRes->status !== 'reserved') {
                return; // Already released, committed, or expired
            }

            $balance = InventoryBalance::where('warehouse_id', $lockedRes->warehouse_id)
                ->where('product_variant_id', $lockedRes->product_variant_id)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $beforeReserved = (string) $balance->reserved_quantity;
                $afterReserved = bcsub($beforeReserved, (string) $lockedRes->quantity, 3);
                if (bccomp($afterReserved, '0.000', 3) < 0) {
                    $afterReserved = '0.000';
                }
                $balance->update(['reserved_quantity' => $afterReserved]);

                InventoryMovement::create([
                    'warehouse_id' => $balance->warehouse_id,
                    'product_variant_id' => $balance->product_variant_id,
                    'type' => 'release',
                    'quantity' => $lockedRes->quantity,
                    'balance_before' => $beforeReserved,
                    'balance_after' => $afterReserved,
                    'reason' => $reason,
                    'reference_type' => 'InventoryReservation',
                    'reference_id' => $lockedRes->id,
                    'user_id' => null,
                    'created_at' => now(),
                ]);
            }

            $lockedRes->update(['status' => 'released']);
        });
    }

    /**
     * Find and atomically release all expired reservations once and only once.
     */
    public function releaseExpiredReservations(): int
    {
        $expired = InventoryReservation::where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expired as $res) {
            DB::transaction(function () use ($res, &$count) {
                $lockedRes = InventoryReservation::where('id', $res->id)
                    ->where('status', 'reserved')
                    ->lockForUpdate()
                    ->first();

                if (!$lockedRes) {
                    return;
                }

                $balance = InventoryBalance::where('warehouse_id', $lockedRes->warehouse_id)
                    ->where('product_variant_id', $lockedRes->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($balance) {
                    $before = (string) $balance->reserved_quantity;
                    $after = bcsub($before, (string) $lockedRes->quantity, 3);
                    if (bccomp($after, '0.000', 3) < 0) {
                        $after = '0.000';
                    }
                    $balance->update(['reserved_quantity' => $after]);

                    InventoryMovement::create([
                        'warehouse_id' => $balance->warehouse_id,
                        'product_variant_id' => $balance->product_variant_id,
                        'type' => 'release',
                        'quantity' => $lockedRes->quantity,
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'reason' => "Expired reservation TTL release #{$lockedRes->id}",
                        'reference_type' => 'InventoryReservation',
                        'reference_id' => $lockedRes->id,
                        'created_at' => now(),
                    ]);
                }

                $lockedRes->update(['status' => 'expired']);
                $count++;
            });
        }

        return $count;
    }
}
