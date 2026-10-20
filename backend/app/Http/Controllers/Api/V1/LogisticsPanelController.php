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
     * List all shipments for logistics operators and dispatchers.
     */
    public function shipments(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $query = Shipment::with(['order.user', 'suborder.seller', 'courier']);

        if ($status) {
            $query->where('status', $status);
        }

        $shipments = $query->latest()->paginate(20);

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

            return ApiResponse::success($updated, "Jo'natma holati yangilandi.");
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_SHIPMENT_TRANSITION', [], 422);
        }
    }
}
