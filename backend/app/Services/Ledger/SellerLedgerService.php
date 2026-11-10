<?php

namespace App\Services\Ledger;

use App\Models\Order;
use App\Models\Refund;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\SellerPayout;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SellerLedgerService
{
    /** Days a sale's money is kept back in case the customer returns the goods. */
    public const HOLD_DAYS = 7;

    public const MIN_PAYOUT = 10000;

    /** Requests that still reserve money. */
    public const OPEN_PAYOUT_STATUSES = ['requested', 'held', 'processing'];

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
        if ($this->hasOpenDispute($seller)) {
            return [
                'allowed' => false,
                'reason' => "Hal qilinmagan qaytarish so'rovi bor (Dispute block). U yopilgach pul yechish mumkin bo'ladi.",
            ];
        }

        $summary = $this->summary($seller);
        $money = fn(int $sum) => number_format($sum, 0, '.', ' ') . ' UZS';

        // 2. Check total ledger balance
        if ($amount > $summary['balance']) {
            return [
                'allowed' => false,
                'reason' => "So'ralgan summa ({$money($amount)}) balansdan ({$money($summary['balance'])}) ko'p.",
            ];
        }

        // 3. Money already asked for cannot be asked for twice
        if ($summary['pending_payouts'] > 0 && $amount > $summary['balance'] - $summary['pending_payouts']) {
            return [
                'allowed' => false,
                'reason' => "Ko'rib chiqilayotgan so'rovingiz bor ({$money($summary['pending_payouts'])}). Hozir yechish mumkin: {$money($summary['available'])}.",
            ];
        }

        // 4. Check hold period on recent sales
        if ($amount > $summary['available']) {
            return [
                'allowed' => false,
                'reason' => "Oxirgi " . self::HOLD_DAYS . " kunlik savdo puli hali kutish muddatida (Hold period). Hozir yechish mumkin: {$money($summary['available'])}.",
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'available_amount' => $summary['available'],
        ];
    }

    /**
     * Returns still being decided could take money back from the seller, so payouts wait for them.
     */
    public function hasOpenDispute(Seller $seller): bool
    {
        return Refund::whereHas('suborder', fn($q) => $q->where('seller_id', $seller->id))
            ->whereIn('status', ['requested', 'approved'])
            ->exists();
    }

    /**
     * Everything the seller needs to know about their money, in one place.
     *
     * balance   = all income minus all deductions
     * on_hold   = net income of the last HOLD_DAYS days (kept back in case of a return)
     * available = balance - on_hold - payouts already asked for
     */
    public function summary(Seller|int $seller): array
    {
        $sellerId = $seller instanceof Seller ? $seller->id : $seller;

        $totals = SellerLedger::where('seller_id', $sellerId)
            ->selectRaw('entry_type, type, SUM(amount) as total')
            ->groupBy('entry_type', 'type')
            ->get();

        $byEntry = fn(string $entryType) => (int) $totals->where('entry_type', $entryType)->sum('total');
        $balance = (int) $totals->where('type', 'credit')->sum('total') - (int) $totals->where('type', 'debit')->sum('total');

        $holdFrom = now()->subDays(self::HOLD_DAYS);
        $recentIncome = (int) SellerLedger::where('seller_id', $sellerId)
            ->where('type', 'credit')
            ->where('created_at', '>=', $holdFrom)
            ->sum('amount');
        $recentCommission = (int) SellerLedger::where('seller_id', $sellerId)
            ->where('entry_type', 'commission_fee')
            ->where('created_at', '>=', $holdFrom)
            ->sum('amount');
        $onHold = max(0, min($balance, $recentIncome - $recentCommission));

        $pendingPayouts = (int) SellerPayout::where('seller_id', $sellerId)
            ->whereIn('status', self::OPEN_PAYOUT_STATUSES)
            ->sum('amount');

        return [
            'balance' => $balance,
            'on_hold' => $onHold,
            'pending_payouts' => $pendingPayouts,
            'available' => max(0, $balance - $onHold - $pendingPayouts),
            'total_sales' => $byEntry('order_sale'),
            'total_commission' => $byEntry('commission_fee'),
            'total_refunds' => $byEntry('refund_deduction'),
            'total_paid_out' => $byEntry('payout'),
        ];
    }

    /**
     * Seller asks for their money. The request only reserves the sum; the ledger changes when an admin pays it.
     *
     * @throws InvalidArgumentException when the seller may not take this sum out right now
     */
    public function requestPayout(Seller $seller, int $amount, array $bankDetails, ?string $notes = null): SellerPayout
    {
        return DB::transaction(function () use ($seller, $amount, $bankDetails, $notes) {
            // Serialise requests of one store so two clicks cannot both pass the check
            Seller::where('id', $seller->id)->lockForUpdate()->first();

            if ($amount < self::MIN_PAYOUT) {
                throw new InvalidArgumentException("Eng kam yechish summasi: " . number_format(self::MIN_PAYOUT, 0, '.', ' ') . " UZS.");
            }

            $eligibility = $this->canRequestPayout($seller, $amount);
            if (!$eligibility['allowed']) {
                throw new InvalidArgumentException($eligibility['reason']);
            }

            return SellerPayout::create([
                'seller_id' => $seller->id,
                'amount' => $amount,
                'status' => 'requested',
                'bank_details' => $bankDetails,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Admin's decision on a payout request. Paying it writes the debit to the ledger exactly once.
     *
     * @throws InvalidArgumentException when the request is already closed or the store lacks the money
     */
    public function reviewPayout(SellerPayout $payout, string $status, ?string $adminNotes, User $admin): SellerPayout
    {
        if (!in_array($status, ['processing', 'completed', 'rejected'], true)) {
            throw new InvalidArgumentException("Noto'g'ri holat.");
        }

        return DB::transaction(function () use ($payout, $status, $adminNotes, $admin) {
            $locked = SellerPayout::where('id', $payout->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isOpen()) {
                throw new InvalidArgumentException("Bu so'rov allaqachon yopilgan.");
            }

            $oldStatus = $locked->status;

            if ($status === 'completed') {
                $balance = $this->getSellerBalance($locked->seller_id);
                if ($locked->amount > $balance) {
                    throw new InvalidArgumentException(
                        "Do'kon balansi yetarli emas (" . number_format($balance, 0, '.', ' ') . " UZS). So'rovni rad eting."
                    );
                }

                SellerLedger::create([
                    'seller_id' => $locked->seller_id,
                    'type' => 'debit',
                    'entry_type' => 'payout',
                    'amount' => $locked->amount,
                    'currency' => 'UZS',
                    'running_balance' => $balance - $locked->amount,
                    'reference' => 'PAYOUT-' . $locked->id,
                    'notes' => "Sotuvchiga pul to'lab berildi: so'rov #{$locked->id}",
                    'created_at' => now(),
                ]);
            }

            $locked->update([
                'status' => $status,
                'admin_notes' => $adminNotes ?? $locked->admin_notes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            AuditService::log('payout.reviewed', $locked, ['status' => $oldStatus], ['status' => $status, 'amount' => $locked->amount], $admin);

            return $locked;
        });
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
