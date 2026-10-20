<?php

namespace App\Services\Refund;

use App\Models\AuditLog;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Ledger\SellerLedgerService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundService
{
    public function __construct(
        protected SellerLedgerService $ledgerService
    ) {}

    /**
     * Customer submits a refund request for an order or suborder.
     */
    public function requestRefund(
        User $user,
        Order $order,
        int $amount,
        string $reason,
        ?Suborder $suborder = null
    ): Refund {
        // Order must be paid or processing
        $payment = Payment::where('order_id', $order->id)->where('status', 'paid')->first();
        if (!$payment) {
            throw new InvalidArgumentException("Faqat to'langan buyurtmalar uchun qaytarish so'rash mumkin.");
        }

        // Prevent over-refunding
        $alreadyRefunded = Refund::where('order_id', $order->id)
            ->whereIn('status', ['requested', 'approved', 'goods_returned', 'completed'])
            ->sum('amount');

        $maxRefundable = $order->grand_total - $alreadyRefunded;
        if ($amount > $maxRefundable) {
            throw new InvalidArgumentException(
                "Qaytarish summasi ({$amount} UZS) ruxsat etilgan maksimal qoldiqdan ({$maxRefundable} UZS) ko'p bo'lishi mumkin emas."
            );
        }

        $refundNumber = 'REF-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $refund = Refund::create([
            'order_id' => $order->id,
            'suborder_id' => $suborder?->id ?? $order->suborders->first()?->id,
            'user_id' => $user->id,
            'refund_number' => $refundNumber,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'requested',
            'is_inventory_restocked' => false,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'refund.requested',
            'entity_type' => 'Refund',
            'entity_id' => $refund->id,
            'old_values' => [],
            'new_values' => [
                'refund_number' => $refund->refund_number,
                'amount' => $amount,
                'reason' => $reason,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $refund;
    }

    /**
     * Moderator approves or rejects refund request.
     */
    public function reviewRefund(Refund $refund, User $moderator, bool $approve, ?string $notes = null): Refund
    {
        if ($refund->status !== 'requested') {
            throw new InvalidArgumentException("Ushbu ariza avval ko'rib chiqilgan (Joriy holat: {$refund->status}).");
        }

        $newStatus = $approve ? 'approved' : 'rejected';

        $refund->update([
            'status' => $newStatus,
            'moderator_id' => $moderator->id,
            'moderator_notes' => $notes,
        ]);

        AuditLog::create([
            'user_id' => $moderator->id,
            'action' => 'refund.reviewed',
            'entity_type' => 'Refund',
            'entity_id' => $refund->id,
            'old_values' => ['status' => 'requested'],
            'new_values' => ['status' => $newStatus, 'notes' => $notes],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $refund;
    }

    /**
     * Inspection of returned physical goods at warehouse and completing the refund:
     * Restocks inventory, deducts from seller's ledger, updates payment status.
     * Guaranteed single-execution / idempotent.
     */
    public function completeRefundWithGoodsRestock(Refund $refund, User $operator): array
    {
        return DB::transaction(function () use ($refund, $operator) {
            $lockedRefund = Refund::where('id', $refund->id)->lockForUpdate()->firstOrFail();

            if ($lockedRefund->status === 'completed') {
                return [
                    'status' => 'already_completed',
                    'message' => 'Qaytarish avval yakunlangan.',
                ];
            }

            if (!in_array($lockedRefund->status, ['approved', 'goods_returned'])) {
                throw new InvalidArgumentException("Faqat tasdiqlangan (approved) arizalar yakunlanishi mumkin.");
            }

            $suborder = Suborder::with(['items', 'seller'])->findOrFail($lockedRefund->suborder_id);
            $order = Order::findOrFail($lockedRefund->order_id);

            // 1. Restock physical inventory back to seller's active warehouse
            if (!$lockedRefund->is_inventory_restocked) {
                $warehouse = Warehouse::where('seller_id', $suborder->seller_id)
                    ->where('is_active', true)
                    ->first();

                if ($warehouse && $suborder->items->isNotEmpty()) {
                    foreach ($suborder->items as $item) {
                        $balance = InventoryBalance::where('warehouse_id', $warehouse->id)
                            ->where('product_variant_id', $item->product_variant_id)
                            ->lockForUpdate()
                            ->first();

                        if ($balance) {
                            $before = (string) $balance->quantity;
                            $after = bcadd($before, (string) $item->quantity, 3);
                            $balance->update(['quantity' => $after]);

                            InventoryMovement::create([
                                'warehouse_id' => $warehouse->id,
                                'product_variant_id' => $item->product_variant_id,
                                'type' => 'inflow',
                                'quantity' => $item->quantity,
                                'balance_before' => $before,
                                'balance_after' => $after,
                                'reason' => "Qaytarilgan tovar qabuli: Refund #{$lockedRefund->refund_number}",
                                'reference_type' => 'Refund',
                                'reference_id' => $lockedRefund->id,
                                'user_id' => $operator->id,
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // 2. Record Debit Deduction in Seller Ledger
            $this->ledgerService->recordRefundDeduction($lockedRefund, $suborder);

            // 3. Update Payment Status
            $payment = Payment::where('order_id', $order->id)->where('status', 'paid')->first();
            if ($payment) {
                $totalRefunded = Refund::where('order_id', $order->id)
                    ->where('status', 'completed')
                    ->sum('amount') + $lockedRefund->amount;

                $paymentStatus = $totalRefunded >= $order->grand_total ? 'refunded' : 'partially_refunded';
                $payment->update(['status' => $paymentStatus]);
            }

            // 4. Mark Refund Completed
            $lockedRefund->update([
                'status' => 'completed',
                'is_inventory_restocked' => true,
            ]);

            AuditLog::create([
                'user_id' => $operator->id,
                'action' => 'refund.restocked',
                'entity_type' => 'Refund',
                'entity_id' => $lockedRefund->id,
                'old_values' => ['status' => 'approved', 'is_inventory_restocked' => false],
                'new_values' => ['status' => 'completed', 'is_inventory_restocked' => true],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'status' => 'completed',
                'refund_id' => $lockedRefund->id,
                'amount' => $lockedRefund->amount,
                'restocked' => true,
            ];
        });
    }
}
