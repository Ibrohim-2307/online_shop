<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Checkout\InventoryReservationService;
use Illuminate\Support\Facades\DB;

/**
 * One set of rules for cancelling an order, whether the customer or an administrator does it.
 */
class OrderCancellationService
{
    public function __construct(protected InventoryReservationService $reservations) {}

    /**
     * Cancellable while unpaid online or cash-on-delivery, and no parcel has been picked up yet.
     * Paid orders go through the refund flow instead, so money is never lost track of.
     */
    public function isCancellable(Order $order): bool
    {
        if (!in_array($order->status, ['pending_payment', 'processing'], true)) {
            return false;
        }

        if ($order->payments->contains('status', 'paid')) {
            return false;
        }

        return $order->suborders->flatMap->shipments
            ->every(fn($shipment) => in_array($shipment->status, ['pending', 'assigned'], true));
    }

    /**
     * Why an order cannot be cancelled, in words for the person asking.
     */
    public function refusalReason(Order $order): string
    {
        if ($order->status === 'cancelled') {
            return "Buyurtma allaqachon bekor qilingan.";
        }
        if ($order->status === 'completed') {
            return "Buyurtma yetkazib bo'lingan. Kerak bo'lsa, qaytarish so'rovi orqali pulni qaytaring.";
        }
        if ($order->payments->contains('status', 'paid')) {
            return "Buyurtma puli to'langan. Uni bekor qilish uchun qaytarish (refund) orqali pulni qaytaring.";
        }

        return "Jo'natma kuryerga topshirilgan, endi bekor qilib bo'lmaydi.";
    }

    /**
     * Release reserved stock and mark the order, its parts and its parcels cancelled.
     */
    public function cancel(Order $order, User $actor, string $auditAction, ?string $reason, string $shipmentNote): void
    {
        $order->loadMissing(['suborders.shipments', 'payments', 'reservations']);

        DB::transaction(function () use ($order, $actor, $auditAction, $reason, $shipmentNote) {
            $previousStatus = $order->status;

            foreach ($order->reservations as $reservation) {
                $this->reservations->releaseReservation($reservation, $shipmentNote . ': ' . $order->order_number, true);
            }

            $order->update(['status' => 'cancelled']);
            foreach ($order->suborders as $suborder) {
                $suborder->update(['status' => 'cancelled']);
                foreach ($suborder->shipments as $shipment) {
                    $shipment->update(['status' => 'failed', 'delay_reason' => $shipmentNote]);
                }
            }
            $order->payments()->where('status', 'pending')->update(['status' => 'cancelled']);

            AuditService::log(
                $auditAction,
                $order,
                ['status' => $previousStatus],
                ['status' => 'cancelled', 'reason' => $reason],
                $actor
            );
        });
    }
}
