<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Tracking\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setupUserWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], [
            'label' => ucfirst($roleName),
        ]);

        $user = User::create([
            'name' => ucfirst($roleName) . ' Test',
            'email' => $email,
            'phone' => '+99890' . rand(1000000, 9999999),
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    protected function setupShipment(User $customer, User $courier, string $otp = '5821'): Shipment
    {
        $seller = Seller::create([
            'name' => 'Fast Store ' . uniqid(),
            'slug' => 'fast-store-' . uniqid(),
            'legal_name' => 'Fast Store MCHJ',
            'tax_id' => '30' . rand(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.9,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . uniqid(),
            'status' => 'processing',
            'currency' => 'UZS',
            'items_total' => 150000,
            'shipping_total' => 20000,
            'grand_total' => 170000,
            'shipping_address' => [
                'recipient_name' => 'Xaridor Ali',
                'phone' => '+998901112233',
                'address' => 'Toshkent sh.',
            ],
        ]);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'SUB-' . uniqid(),
            'status' => 'shipped',
            'items_subtotal' => 150000,
            'shipping_cost' => 20000,
            'commission_rate' => 5.0,
            'commission_amount' => 7500,
            'total_amount' => 170000,
        ]);

        return Shipment::create([
            'suborder_id' => $suborder->id,
            'tracking_number' => 'TRK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'courier_id' => $courier->id,
            'status' => 'in_transit',
            'delivery_otp' => $otp,
        ]);
    }

    public function test_courier_cannot_mark_delivered_without_otp(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust1@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier1@example.uz');
        $shipment = $this->setupShipment($customer, $courier, '4321');

        $response = $this->actingAs($courier)
            ->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
                'status' => 'delivered',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'INVALID_SHIPMENT_TRANSITION',
            ],
        ]);
        $this->assertStringContainsString('tasdiqlash kodi (OTP) kiritilishi shart', $response->json('error.message'));
        $this->assertEquals('in_transit', $shipment->fresh()->status);
    }

    public function test_courier_cannot_mark_delivered_with_incorrect_otp(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust2@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier2@example.uz');
        $shipment = $this->setupShipment($customer, $courier, '7890');

        $response = $this->actingAs($courier)
            ->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
                'status' => 'delivered',
                'otp_code' => '0000',
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('noto\'g\'ri', $response->json('error.message'));
        $this->assertEquals('in_transit', $shipment->fresh()->status);
    }

    public function test_courier_marks_delivered_successfully_with_correct_otp(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust3@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier3@example.uz');
        $shipment = $this->setupShipment($customer, $courier, '6543');

        $response = $this->actingAs($courier)
            ->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
                'status' => 'delivered',
                'otp_code' => '6543',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('delivered', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->delivered_at);
    }

    public function test_customer_can_see_delivery_otp_in_tracking_summary(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust4@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier4@example.uz');
        $shipment = $this->setupShipment($customer, $courier, '9182');

        $trackingService = app(TrackingService::class);

        // Customer sees the OTP
        $customerSummary = $trackingService->getTrackingSummary($shipment, $customer);
        $this->assertEquals('9182', $customerSummary['delivery_otp']);

        // Courier CANNOT see the OTP
        $courierSummary = $trackingService->getTrackingSummary($shipment, $courier);
        $this->assertNull($courierSummary['delivery_otp']);
    }
}
