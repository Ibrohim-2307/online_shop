<?php

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShipmentTransitionService
{
    /**
     * Allowed status transitions for shipments:
     * pending -> assigned -> picked_up -> in_transit -> delivered
     * Branching: in_transit -> delayed -> in_transit, in_transit -> failed
     */
    protected array $allowedTransitions = [
        'pending' => ['assigned', 'cancelled'],
        'assigned' => ['picked_up', 'pending', 'cancelled'],
        'picked_up' => ['in_transit', 'delayed'],
        'in_transit' => ['delivered', 'delayed', 'failed'],
        'delayed' => ['in_transit', 'delivered', 'failed'],
        'delivered' => [], // Terminal
        'failed' => ['pending'], // Can be retried
        'cancelled' => [], // Terminal
    ];

    /**
     * Assign courier to shipment.
     */
    public function assignCourier(Shipment $shipment, User $courier, ?User $operator = null): Shipment
    {
        if (!in_array($shipment->status, ['pending', 'assigned'])) {
            throw new InvalidArgumentException("Ushbu jo'natmaga kuryer biriktirish mumkin emas (Joriy holat: {$shipment->status}).");
        }

        $shipment->update([
            'courier_id' => $courier->id,
            'status' => 'assigned',
        ]);

        return $shipment;
    }

    /**
     * Transition shipment status through the strict state machine.
     * Enforces required delay_reason on 'delayed' and 'failed' statuses.
     * Guards parent order from premature completion when only partial shipments are delivered.
     */
    public function transitionStatus(Shipment $shipment, string $newStatus, ?string $delayReason = null): Shipment
    {
        $currentStatus = $shipment->status;

        // Verify valid transition
        $allowedNext = $this->allowedTransitions[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowedNext)) {
            throw new InvalidArgumentException(
                "Holatni '{$currentStatus}' dan '{$newStatus}' ga o'zgartirish taqiqlangan (Ruxsat etilgan: " . implode(', ', $allowedNext) . ")."
            );
        }

        // Reason enforcement
        if (in_array($newStatus, ['delayed', 'failed']) && empty($delayReason)) {
            throw new InvalidArgumentException("Jo'natma '{$newStatus}' holatiga o'tkazilganda kechikish yoki muammo sababi kiritilishi shart.");
        }

        return DB::transaction(function () use ($shipment, $newStatus, $delayReason) {
            $updateData = ['status' => $newStatus];

            if ($delayReason !== null) {
                $updateData['delay_reason'] = $delayReason;
            }

            if ($newStatus === 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $shipment->update($updateData);

            // Update associated Suborder
            $suborder = Suborder::find($shipment->suborder_id);
            if ($suborder) {
                if ($newStatus === 'delivered') {
                    $suborder->update(['status' => 'delivered']);
                } elseif ($newStatus === 'in_transit') {
                    $suborder->update(['status' => 'shipped']);
                } elseif ($newStatus === 'picked_up') {
                    $suborder->update(['status' => 'preparing']);
                }
            }

            // Guard Parent Order completion
            if ($suborder && $suborder->order_id) {
                $this->evaluateParentOrderCompletion($suborder->order_id);
            }

            return $shipment->fresh();
        });
    }

    /**
     * Check if ALL suborders/shipments of a parent order are delivered.
     * If even one suborder is NOT delivered, the parent order MUST NOT be marked completed.
     */
    public function evaluateParentOrderCompletion(?int $orderId): void
    {
        if (!$orderId) {
            return;
        }

        $order = Order::with('suborders.shipments')->find($orderId);
        if (!$order) {
            return;
        }

        // If order is cancelled, do nothing
        if ($order->status === 'cancelled') {
            return;
        }

        $allSubordersDelivered = true;
        foreach ($order->suborders as $suborder) {
            if ($suborder->status !== 'delivered') {
                $allSubordersDelivered = false;
                break;
            }
        }

        if ($allSubordersDelivered && $order->suborders->isNotEmpty()) {
            $order->update(['status' => 'completed']);
        } else {
            // Ensure parent order stays in processing (partial shipment rule)
            if ($order->status === 'completed') {
                $order->update(['status' => 'processing']);
            }
        }
    }
}
