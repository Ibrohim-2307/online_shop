<?php

namespace App\Services\Ledger;

use App\Models\Order;
use App\Models\Refund;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\SellerPayout;
use App\Models\Suborder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SellerLedgerService
{
    /**
     * Calculate current net ledger balance of a seller by summing all immutable ledger credits and debits.
     * Balance = SUM(credit) - SUM(debit).
     */
    public function getSellerBalance(Seller|int $seller): int
    {
        $sellerId = $seller instanceof Seller ? $seller->id : $seller;

        $credits = SellerLedger::where('seller_id', $sellerId)
            ->where('type', 'credit')
            ->sum('amount');

        $debits = SellerLedger::where('seller_id', $sellerId)
            ->where('type', 'debit')
            ->sum('amount');

        return (int) ($credits - $debits);
    }

    /**
     * Record an order sale and platform commission fee upon verified customer payment.
     * Uses atomic transaction and computes append-only running balance.
     */
    public function recordOrderSale(Suborder $suborder): array
    {
        return DB::transaction(function () use ($suborder) {
            $sellerId = $suborder->seller_id;
            $currentBalance = $this->getSellerBalance($sellerId);

            // 1. Credit Gross Items Subtotal
            $saleAmount = (int) $suborder->items_subtotal;
            $newBalance1 = $currentBalance + $saleAmount;

            $creditEntry = SellerLedger::create([
                'seller_id' => $sellerId,
                'suborder_id' => $suborder->id,
                'order_id' => $suborder->order_id,
                'type' => 'credit',
                'entry_type' => 'order_sale',
                'amount' => $saleAmount,
                'currency' => 'UZS',
                'running_balance' => $newBalance1,
                'reference' => $suborder->suborder_number,
                'notes' => "Savdo tushumi: Suborder #{$suborder->suborder_number}",
                'created_at' => now(),
            ]);

            // 2. Debit Platform Commission Fee
            $commAmount = (int) $suborder->commission_amount;
            $debitEntry = null;

            if ($commAmount > 0) {
                $newBalance2 = $newBalance1 - $commAmount;

                $debitEntry = SellerLedger::create([
                    'seller_id' => $sellerId,
                    'suborder_id' => $suborder->id,
                    'order_id' => $suborder->order_id,
                    'type' => 'debit',
                    'entry_type' => 'commission_fee',
                    'amount' => $commAmount,
                    'currency' => 'UZS',
                    'running_balance' => $newBalance2,
                    'reference' => $suborder->suborder_number,
                    'notes' => "Platforma komissiyasi ({$suborder->commission_rate}%): Suborder #{$suborder->suborder_number}",
                    'created_at' => now(),
                ]);
            }

            return [
                'credit_entry' => $creditEntry,
                'debit_entry' => $debitEntry,
                'final_balance' => $this->getSellerBalance($sellerId),
            ];
        });
    }

    /**
     * Record refund deduction debit against seller's ledger.
     */
    public function recordRefundDeduction(Refund $refund, Suborder $suborder): SellerLedger
    {
        return DB::transaction(function () use ($refund, $suborder) {
            $sellerId = $suborder->seller_id;
            $currentBalance = $this->getSellerBalance($sellerId);
            $deduction = (int) $refund->amount;
            $newBalance = $currentBalance - $deduction;

            return SellerLedger::create([
                'seller_id' => $sellerId,
                'suborder_id' => $suborder->id,
                'order_id' => $suborder->order_id,
                'type' => 'debit',
                'entry_type' => 'refund_deduction',
                'amount' => $deduction,
                'currency' => 'UZS',
                'running_balance' => $newBalance,
                'reference' => $refund->refund_number,
                'notes' => "Tovarni qaytarish chegirishi: Refund #{$refund->refund_number}",
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Check if seller is eligible to request payout (evaluates dispute blocks, hold periods).
     */
    public function canRequestPayout(Seller $seller, int $amount): array
    {
        // 1. Check dispute block
        $hasActiveDispute = Refund::whereHas('suborder', fn($q) => $q->where('seller_id', $seller->id))
            ->whereIn('status', ['requested', 'approved'])
            ->exists();

        if ($hasActiveDispute) {
            return [
                'allowed' => false,
                'reason' => "Sotuvchida hal qilinmagan nizoli qaytarish arizalari mavjud (Dispute block).",
            ];
        }

        // 2. Check total ledger balance
        $currentBalance = $this->getSellerBalance($seller);
        if ($amount > $currentBalance) {
            return [
                'allowed' => false,
                'reason' => "Yechib olish so'ralgan mablag' ({$amount} UZS) mavjud balanstdan ({$currentBalance} UZS) ko'p.",
            ];
        }

        // 3. Check 7-day hold period on recent sales
        $recentCredits = SellerLedger::where('seller_id', $seller->id)
            ->where('type', 'credit')
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('amount');

        $availableAfterHold = $currentBalance - $recentCredits;
        if ($availableAfterHold < $amount) {
            return [
                'allowed' => false,
                'reason' => "Oxirgi 7 kunlik savdo tushumlari xavfsizlik tekshiruvida (Hold period). Erkin yechish mumkin: " . max(0, $availableAfterHold) . " UZS.",
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'available_amount' => $availableAfterHold,
        ];
    }

    /**
     * Verify strict ledger balance equality across the system:
     * Customer Paid Grand Total = Sum(Seller Net Revenues) + Sum(Platform Commissions) + Sum(Shipping Costs).
     */
    public function verifyOrderBalanceEquality(Order $order): bool
    {
        $paidAmount = $order->grand_total;

        $itemsTotal = 0;
        $shippingTotal = 0;
        $commissionTotal = 0;

        foreach ($order->suborders as $suborder) {
            $itemsTotal += $suborder->items_subtotal;
            $shippingTotal += $suborder->shipping_cost;
            $commissionTotal += $suborder->commission_amount;
        }

        $expectedGrandTotal = $itemsTotal + $shippingTotal;

        return $paidAmount === $expectedGrandTotal;
    }
}
