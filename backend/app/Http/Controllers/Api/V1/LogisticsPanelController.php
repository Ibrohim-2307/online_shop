<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Logistics\ShipmentTransitionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class LogisticsPanelController extends Controller
{
    public function __construct(
        protected ShipmentTransitionService $transitionService
    ) {}

    /**
     * List shipments for logistics operators, dispatchers, and couriers.
     */
    public function shipments(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status');
        $type = $request->query('type');
        $search = $request->query('search');

        $query = Shipment::with(['order.user', 'suborder.seller', 'suborder.items', 'courier']);

        // If courier role, scope to their assigned shipments or available
        if ($user && $user->hasRole('courier') && !$user->hasRole(['admin', 'logistics_operator'])) {
            $query->where(function ($q) use ($user) {
                $q->where('courier_id', $user->id)
                  ->orWhereNull('courier_id');
            });
        }

        if ($type) {
            if ($type === 'today') {
                $query->whereIn('status', ['assigned', 'picked_up', 'in_transit']);
            } elseif ($type === 'pickup') {
                $query->whereIn('status', ['assigned', 'ready_for_pickup']);
            } elseif ($type === 'in_transit') {
                $query->whereIn('status', ['picked_up', 'in_transit']);
            } elseif ($type === 'completed') {
                $query->where('status', 'delivered');
            } elseif ($type === 'cod') {
                $query->whereHas('order.payments', fn($pq) => $pq->where('payment_method', 'cash_on_delivery'));
            }
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('tracking_number', 'like', "%{$term}%")
                  ->orWhere('shipping_address', 'like', "%{$term}%")
                  ->orWhereHas('order.user', fn($uq) => $uq->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"))
                  ->orWhereHas('suborder.seller', fn($sq) => $sq->where('name', 'like', "%{$term}%"));
            });
        }

        $shipments = $query->latest()->paginate($request->integer('per_page', 25));

        return ApiResponse::success($shipments);
    }

    /**
     * Assign courier to a shipment.
     */
    public function assignCourier(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
        ]);

        $shipment = Shipment::findOrFail($id);
        $courier = User::findOrFail($validated['courier_id']);

        try {
            $updated = $this->transitionService->assignCourier($shipment, $courier, $request->user());
            return ApiResponse::success($updated, "Kuryer jo'natmaga muvaffaqiyatli biriktirildi.");
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'COURIER_ASSIGNMENT_FAILED', [], 422);
        }
    }

    /**
     * Transition shipment status through strict state machine.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:assigned,picked_up,in_transit,delayed,delivered,failed,cancelled',
            'delay_reason' => 'nullable|string|max:255',
        ]);

        $shipment = Shipment::findOrFail($id);

        try {
            $updated = $this->transitionService->transitionStatus(
                $shipment,
                $validated['status'],
                $validated['delay_reason'] ?? null
            );
            return ApiResponse::success($updated, "Jo'natma holati muvaffaqiyatli yangilandi.");
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_SHIPMENT_TRANSITION', [], 422);
        }
    }
}
