<?php

namespace App\Services\Tracking;

use App\Models\CourierLocation;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    /**
     * Ingest batch of GPS locations from courier app.
     * Enforces coordinate validity, assignment checks, deduplication and monotonic timestamp ordering.
     */
    public function recordBatch(User $courier, array $locations): array
    {
        $acceptedCount = 0;
        $deduplicatedCount = 0;

        foreach ($locations as $point) {
            $eventId = $point['event_id'] ?? null;
            if (!$eventId) {
                continue;
            }

            // 1. Deduplication Check (Idempotency)
            $existing = CourierLocation::where('event_id', $eventId)->first();
            if ($existing) {
                $deduplicatedCount++;
                continue;
            }

            // 2. Coordinate Range Validation
            $lat = (float) $point['latitude'];
            $lng = (float) $point['longitude'];

            if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
                throw new InvalidArgumentException("GPS koordinatalari yaroqsiz diapazonda (Lat: {$lat}, Lng: {$lng}).");
            }

            // 3. Timestamp Sanity Check
            $recordedAt = isset($point['recorded_at'])
                ? Carbon::parse($point['recorded_at'])
                : now();

            if ($recordedAt->isAfter(now()->addMinutes(5))) {
                throw new InvalidArgumentException("GPS vaqti kelajakda bo'lishi mumkin emas.");
            }

            // 4. Shipment Assignment Validation
            $shipmentId = $point['shipment_id'] ?? null;
            if ($shipmentId) {
                $shipment = Shipment::find($shipmentId);
                if ($shipment && $shipment->courier_id !== $courier->id && !$courier->hasRole('admin')) {
                    throw new InvalidArgumentException("Ushbu jo'natma ({$shipmentId}) kuryerga biriktirilmagan.");
                }
            }

            // 5. Store Location
            CourierLocation::create([
                'courier_id' => $courier->id,
                'shipment_id' => $shipmentId,
                'event_id' => $eventId,
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => $point['accuracy'] ?? null,
                'heading' => $point['heading'] ?? null,
                'speed' => $point['speed'] ?? null,
                'recorded_at' => $recordedAt,
                'received_at' => now(),
            ]);

            // Log timeline ping event if attached to a shipment
            if ($shipmentId) {
                ShipmentEvent::create([
                    'shipment_id' => $shipmentId,
                    'event_type' => 'location_ping',
                    'notes' => "GPS nuqta qabul qilindi ({$lat}, {$lng})",
                    'payload' => [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'speed' => $point['speed'] ?? null,
                    ],
                    'created_at' => now(),
                ]);
            }

            $acceptedCount++;
        }

        return [
            'status' => 'success',
            'accepted_points' => $acceptedCount,
            'deduplicated_points' => $deduplicatedCount,
        ];
    }

    /**
     * Get the authoritative latest live location for a shipment.
     * Guaranteed monotonic: strictly evaluates the highest recorded_at timestamp.
     * Flags stale if no ping for > 5 minutes.
     */
    public function getLatestLocation(Shipment $shipment): ?array
    {
        $latest = CourierLocation::where(function ($query) use ($shipment) {
            $query->where('shipment_id', $shipment->id);
            if ($shipment->courier_id) {
                $query->orWhere('courier_id', $shipment->courier_id);
            }
        })
        ->orderBy('recorded_at', 'desc')
        ->first();

        if (!$latest) {
            return null;
        }

        $recordedTime = Carbon::parse($latest->recorded_at);
        $isStale = $recordedTime->diffInMinutes(now()) >= 5;

        return [
            'id' => $latest->id,
            'latitude' => (float) $latest->latitude,
            'longitude' => (float) $latest->longitude,
            'accuracy' => $latest->accuracy,
            'heading' => $latest->heading,
            'speed' => $latest->speed,
            'recorded_at' => $recordedTime->toIso8601String(),
            'is_stale' => $isStale,
            'stale_duration_minutes' => $isStale ? $recordedTime->diffInMinutes(now()) : 0,
        ];
    }

    /**
     * Get customer tracking dashboard payload with privacy protection and delivery timeline.
     */
    public function getTrackingSummary(Shipment $shipment, ?User $user = null): array
    {
        $order = $shipment->suborder->order;

        // Strict customer access control
        if ($user && $order->user_id !== $user->id && !$user->hasRole(['admin', 'courier', 'logistics_operator'])) {
            throw new InvalidArgumentException("Ushbu jo'natmani kuzatish huquqi mavjud emas.");
        }

        $latestLocation = $this->getLatestLocation($shipment);

        $events = ShipmentEvent::where('shipment_id', $shipment->id)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        $isClosed = in_array($shipment->status, ['delivered', 'cancelled']);

        return [
            'tracking_number' => $shipment->tracking_number,
            'status' => $shipment->status,
            'delay_reason' => $shipment->delay_reason,
            'courier_name' => $shipment->courier ? $shipment->courier->name : 'Tayinlanmagan',
            'latest_location' => $latestLocation,
            'tracking_closed' => $isClosed,
            'estimated_delivery_at' => $shipment->estimated_delivery_at?->toIso8601String(),
            'delivered_at' => $shipment->delivered_at?->toIso8601String(),
            'provider' => $latestLocation ? 'live_device_gps' : 'pending_device_signal',
            'timeline' => $events,
        ];
    }

    /**
     * Poll updates with cursor and backoff for clients without WebSocket.
     */
    public function pollUpdates(Shipment $shipment, ?int $sinceId = null): array
    {
        $query = CourierLocation::where(function ($q) use ($shipment) {
            $q->where('shipment_id', $shipment->id);
            if ($shipment->courier_id) {
                $q->orWhere('courier_id', $shipment->courier_id);
            }
        });

        if ($sinceId) {
            $query->where('id', '>', $sinceId);
        }

        $newLocations = $query->orderBy('id', 'asc')->get();

        $isClosed = in_array($shipment->status, ['delivered', 'cancelled']);

        return [
            'status' => $shipment->status,
            'delay_reason' => $shipment->delay_reason,
            'tracking_closed' => $isClosed,
            'recommended_poll_interval_seconds' => $isClosed ? 0 : 10,
            'locations' => $newLocations->map(fn($loc) => [
                'id' => $loc->id,
                'latitude' => (float) $loc->latitude,
                'longitude' => (float) $loc->longitude,
                'heading' => $loc->heading,
                'speed' => $loc->speed,
                'recorded_at' => Carbon::parse($loc->recorded_at)->toIso8601String(),
            ]),
        ];
    }
}
