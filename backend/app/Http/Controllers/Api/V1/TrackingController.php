<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\Tracking\TrackingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    /**
     * Courier batch location update.
     */
    public function recordBatch(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'locations' => 'required|array|min:1|max:100',
            'locations.*.event_id' => 'required|string',
            'locations.*.latitude' => 'required|numeric',
            'locations.*.longitude' => 'required|numeric',
            'locations.*.accuracy' => 'nullable|numeric',
            'locations.*.heading' => 'nullable|numeric',
            'locations.*.speed' => 'nullable|numeric',
            'locations.*.recorded_at' => 'nullable|date',
            'locations.*.shipment_id' => 'nullable|exists:shipments,id',
        ]);

        try {
            $result = $this->trackingService->recordBatch($user, $validated['locations']);
            return ApiResponse::success($result, "GPS nuqtalari qabul qilindi.");
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_LOCATION_BATCH', [], 422);
        }
    }

    /**
     * Customer views live tracking for their shipment.
     */
    public function track(Request $request, string $trackingNumber): JsonResponse
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)
            ->with(['suborder.order', 'courier'])
            ->firstOrFail();

        try {
            $summary = $this->trackingService->getTrackingSummary($shipment, $request->user());
            return ApiResponse::success($summary);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'UNAUTHORIZED_TRACKING_ACCESS', [], 403);
        }
    }

    /**
     * Polling fallback endpoint for clients.
     */
    public function poll(Request $request, string $trackingNumber): JsonResponse
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)
            ->with(['suborder.order', 'courier'])
            ->firstOrFail();

        $sinceId = $request->query('since_id') ? (int) $request->query('since_id') : null;

        try {
            // Verify permission
            $this->trackingService->getTrackingSummary($shipment, $request->user());

            $updates = $this->trackingService->pollUpdates($shipment, $sinceId);
            return ApiResponse::success($updates);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'UNAUTHORIZED_TRACKING_ACCESS', [], 403);
        }
    }
}
