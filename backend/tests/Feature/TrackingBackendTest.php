<?php

namespace Tests\Feature;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Tracking\TrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setupUserWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], [
            'label' => ucfirst($roleName),
        ]);

        $user = User::create([
            'name' => ucfirst($roleName) . ' User',
            'email' => $email,
            'phone' => '+99890' . rand(1000000, 9999999),
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    protected function setupShipment(User $customer, User $courier): array
    {
        $seller = Seller::create([
            'name' => 'Fast Logistics Store',
            'slug' => 'fast-store-' . uniqid(),
            'legal_name' => 'Fast Store MCHJ',
            'tax_id' => '30' . rand(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.9,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-TRK-' . uniqid(),
            'status' => 'processing',
            'currency' => 'UZS',
            'items_total' => 150000,
            'shipping_total' => 20000,
            'grand_total' => 170000,
            'shipping_address' => [
                'recipient_name' => 'Mijoz',
                'phone' => '+998901234567',
                'address' => 'Mustaqillik ko\'chasi, 15-uy',
            ],
        ]);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'SUB-TRK-' . uniqid(),
            'status' => 'shipped',
            'items_subtotal' => 150000,
            'shipping_cost' => 20000,
            'commission_rate' => 5.0,
            'commission_amount' => 7500,
            'total_amount' => 170000,
        ]);

        $shipment = Shipment::create([
            'suborder_id' => $suborder->id,
            'tracking_number' => 'TRK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'courier_id' => $courier->id,
            'status' => 'in_transit',
        ]);

        return compact('seller', 'order', 'suborder', 'shipment');
    }

    public function test_another_customer_cannot_track_foreign_shipment(): void
    {
        $customerA = $this->setupUserWithRole('customer', 'cust_a@example.uz');
        $customerB = $this->setupUserWithRole('customer', 'cust_b@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_trk1@example.uz');

        $setup = $this->setupShipment($customerA, $courier);
        $shipment = $setup['shipment'];

        // 1. Customer B attempts to track Customer A's shipment -> 403 Forbidden
        $forbiddenRes = $this->actingAs($customerB, 'sanctum')->getJson("/api/v1/tracking/{$shipment->tracking_number}");
        $forbiddenRes->assertStatus(403)
            ->assertJsonPath('error.code', 'UNAUTHORIZED_TRACKING_ACCESS');

        // 2. Customer A tracks own shipment -> 200 OK
        $ownRes = $this->actingAs($customerA, 'sanctum')->getJson("/api/v1/tracking/{$shipment->tracking_number}");
        $ownRes->assertStatus(200)
            ->assertJsonPath('data.tracking_number', $shipment->tracking_number)
            ->assertJsonPath('data.status', 'in_transit');
    }

    public function test_courier_batch_locations_ingestion_and_deduplication(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_batch@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_batch@example.uz');
        $setup = $this->setupShipment($customer, $courier);
        $shipment = $setup['shipment'];

        $event1 = 'evt_gps_1_' . uniqid();
        $event2 = 'evt_gps_2_' . uniqid();

        $batch = [
            [
                'event_id' => $event1,
                'shipment_id' => $shipment->id,
                'latitude' => 41.311158,
                'longitude' => 69.279737,
                'accuracy' => 4.5,
                'heading' => 90.0,
                'speed' => 12.5,
                'recorded_at' => now()->subMinutes(2)->toIso8601String(),
            ],
            [
                'event_id' => $event2,
                'shipment_id' => $shipment->id,
                'latitude' => 41.312500,
                'longitude' => 69.281000,
                'accuracy' => 3.2,
                'heading' => 95.0,
                'speed' => 14.0,
                'recorded_at' => now()->subMinute()->toIso8601String(),
            ],
            // Duplicate of event1
            [
                'event_id' => $event1,
                'shipment_id' => $shipment->id,
                'latitude' => 41.311158,
                'longitude' => 69.279737,
                'accuracy' => 4.5,
                'recorded_at' => now()->subMinutes(2)->toIso8601String(),
            ],
        ];

        $response = $this->actingAs($courier, 'sanctum')->postJson('/api/v1/courier/location/batch', [
            'locations' => $batch,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.accepted_points', 2)
            ->assertJsonPath('data.deduplicated_points', 1);

        $this->assertEquals(2, CourierLocation::where('shipment_id', $shipment->id)->count());
    }

    public function test_out_of_order_older_batch_does_not_regress_latest_live_marker(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_order@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_order@example.uz');
        $setup = $this->setupShipment($customer, $courier);
        $shipment = $setup['shipment'];
        $trackingService = app(TrackingService::class);

        // 1. Initial point recorded at 10:00:00 (point 1)
        CourierLocation::create([
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'event_id' => 'evt_1000',
            'latitude' => 41.300000,
            'longitude' => 69.200000,
            'recorded_at' => now()->subMinutes(10),
            'received_at' => now()->subMinutes(10),
        ]);

        // 2. Newer point recorded at 10:05:00 (point 2)
        CourierLocation::create([
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'event_id' => 'evt_1005',
            'latitude' => 41.350000,
            'longitude' => 69.250000,
            'recorded_at' => now()->subMinutes(5),
            'received_at' => now()->subMinutes(5),
        ]);

        // 3. Delayed offline packet arrives later, but was recorded at 10:02:00 (point 3 - out of order)
        CourierLocation::create([
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'event_id' => 'evt_1002',
            'latitude' => 41.320000,
            'longitude' => 69.220000,
            'recorded_at' => now()->subMinutes(8), // older than 10:05
            'received_at' => now(), // received later
        ]);

        // Authoritative latest location MUST remain point 2 (41.350, 69.250)
        $latest = $trackingService->getLatestLocation($shipment);
        $this->assertEquals(41.350000, $latest['latitude']);
        $this->assertEquals(69.250000, $latest['longitude']);
    }

    public function test_stale_marker_detection_after_5_minutes(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_stale@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_stale@example.uz');
        $setup = $this->setupShipment($customer, $courier);
        $shipment = $setup['shipment'];
        $trackingService = app(TrackingService::class);

        // 1. Fresh location ping 1 minute ago
        $loc = CourierLocation::create([
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'event_id' => 'evt_fresh_' . uniqid(),
            'latitude' => 41.300000,
            'longitude' => 69.200000,
            'recorded_at' => now()->subMinute(),
            'received_at' => now(),
        ]);

        $summary = $trackingService->getLatestLocation($shipment);
        $this->assertFalse($summary['is_stale']);

        // 2. Simulate stale location ping 8 minutes ago
        $loc->update(['recorded_at' => now()->subMinutes(8)]);

        $summaryStale = $trackingService->getLatestLocation($shipment);
        $this->assertTrue($summaryStale['is_stale']);
        $this->assertGreaterThanOrEqual(8, $summaryStale['stale_duration_minutes']);
    }

    public function test_polling_fallback_returns_deltas_and_closes_upon_delivery(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_poll@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_poll@example.uz');
        $setup = $this->setupShipment($customer, $courier);
        $shipment = $setup['shipment'];

        // Point 1
        $loc1 = CourierLocation::create([
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'event_id' => 'evt_p1_' . uniqid(),
            'latitude' => 41.100000,
            'longitude' => 69.100000,
            'recorded_at' => now()->subMinutes(2),
            'received_at' => now(),
        ]);

        // Point 2
        $loc2 = CourierLocation::create([
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'event_id' => 'evt_p2_' . uniqid(),
            'latitude' => 41.105000,
            'longitude' => 69.105000,
            'recorded_at' => now()->subMinute(),
            'received_at' => now(),
        ]);

        // Client polls with since_id = $loc1->id -> only receives $loc2
        $pollRes = $this->actingAs($customer, 'sanctum')->getJson("/api/v1/tracking/{$shipment->tracking_number}/poll?since_id={$loc1->id}");
        $pollRes->assertStatus(200)
            ->assertJsonPath('data.tracking_closed', false)
            ->assertJsonCount(1, 'data.locations');

        $this->assertEquals($loc2->id, $pollRes->json('data.locations.0.id'));

        // Mark shipment delivered
        $shipment->update(['status' => 'delivered', 'delivered_at' => now()]);

        // Next poll returns tracking_closed: true
        $pollDelivered = $this->actingAs($customer, 'sanctum')->getJson("/api/v1/tracking/{$shipment->tracking_number}/poll");
        $pollDelivered->assertStatus(200)
            ->assertJsonPath('data.tracking_closed', true)
            ->assertJsonPath('data.recommended_poll_interval_seconds', 0);
    }

    public function test_coordinate_validation_rejects_out_of_bounds(): void
    {
        $courier = $this->setupUserWithRole('courier', 'courier_bounds@example.uz');

        $invalidBatch = [
            [
                'event_id' => 'evt_out_of_bounds_' . uniqid(),
                'latitude' => 95.500000, // Invalid: > 90
                'longitude' => 69.200000,
                'recorded_at' => now()->toIso8601String(),
            ],
        ];

        $res = $this->actingAs($courier, 'sanctum')->postJson('/api/v1/courier/location/batch', [
            'locations' => $invalidBatch,
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_LOCATION_BATCH');
    }

    public function test_web_tracking_page_renders_successfully(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_map@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_map@example.uz');
        $setup = $this->setupShipment($customer, $courier);
        $shipment = $setup['shipment'];

        $res = $this->get("/tracking/{$shipment->tracking_number}");
        $res->assertStatus(200)
            ->assertSee($shipment->tracking_number)
            ->assertSee('liveMap')
            ->assertSee('Yetkazib berish xaritasi');
    }

    public function test_web_tracking_json_polling_endpoint(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_json@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier_json@example.uz');
        $setup = $this->setupShipment($customer, $courier);
        $shipment = $setup['shipment'];

        $res = $this->getJson("/tracking/{$shipment->tracking_number}?json=1");
        $res->assertStatus(200)
            ->assertJsonPath('tracking_number', $shipment->tracking_number)
            ->assertJsonStructure([
                'tracking_number',
                'status',
                'courier' => ['name', 'phone', 'vehicle', 'plate'],
                'warehouse' => ['name', 'address', 'lat', 'lng'],
                'destination' => ['recipient', 'phone', 'address', 'lat', 'lng'],
                'latest',
                'locations',
                'eta',
            ]);
    }
}
