<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Rfq;
use App\Models\RfqQuotation;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerPayout;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Ledger\SellerLedgerService;
use App\Services\Logistics\ShipmentTransitionService;
use App\Services\Rfq\RfqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAndAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setupUserWithRole(string $roleName, string $email, ?int $sellerId = null): User
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

        if ($sellerId) {
            \App\Models\SellerMember::create([
                'seller_id' => $sellerId,
                'user_id' => $user->id,
                'role' => 'owner',
                'is_active' => true,
            ]);
        }

        return $user;
    }

    protected function createSeller(string $name, string $slug): Seller
    {
        return Seller::create([
            'name' => $name,
            'slug' => $slug,
            'legal_name' => $name . ' MCHJ',
            'tax_id' => '30' . rand(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.8,
        ]);
    }

    public function test_seller_a_cannot_export_seller_b_data(): void
    {
        $sellerA = $this->createSeller('Seller A Do\'koni', 'seller-a-' . uniqid());
        $sellerB = $this->createSeller('Seller B Do\'koni', 'seller-b-' . uniqid());

        $userA = $this->setupUserWithRole('seller_owner', 'owner_a@example.uz', $sellerA->id);
        $userB = $this->setupUserWithRole('seller_owner', 'owner_b@example.uz', $sellerB->id);

        $category = Category::firstOrCreate(['slug' => 'qurilish-mat'], ['name' => 'Qurilish Materiallari', 'is_active' => true]);

        // Seller A product
        $prodA = Product::create([
            'seller_id' => $sellerA->id,
            'category_id' => $category->id,
            'name' => 'Sement Seller A',
            'slug' => 'sement-a-' . uniqid(),
            'unit' => 'piece',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $prodA->variants()->create(['sku' => 'SKU-A-' . uniqid(), 'name' => 'Var A', 'price' => 50000, 'currency' => 'UZS']);

        // Seller B product
        $prodB = Product::create([
            'seller_id' => $sellerB->id,
            'category_id' => $category->id,
            'name' => 'Gisht Seller B',
            'slug' => 'gisht-b-' . uniqid(),
            'unit' => 'piece',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $prodB->variants()->create(['sku' => 'SKU-B-' . uniqid(), 'name' => 'Var B', 'price' => 2000, 'currency' => 'UZS']);

        // 1. Seller A attempts to export with explicit query seller_id = Seller B
        $forbiddenRes = $this->actingAs($userA, 'sanctum')->getJson("/api/v1/seller/export/products?seller_id={$sellerB->id}");
        $forbiddenRes->assertStatus(403)
            ->assertJsonPath('error.code', 'TENANT_ISOLATION_VIOLATION');

        // 2. Seller A exports own products
        $ownRes = $this->actingAs($userA, 'sanctum')->getJson('/api/v1/seller/export/products');
        $ownRes->assertStatus(200)
            ->assertJsonPath('data.seller_id', $sellerA->id)
            ->assertJsonPath('data.count', 1);

        $records = $ownRes->json('data.records');
        $this->assertEquals($prodA->id, $records[0]['id']);
        $this->assertNotEquals($prodB->id, $records[0]['id']);
    }

    public function test_support_role_cannot_approve_payout_only_admin_can(): void
    {
        $seller = $this->createSeller('Mega Qurilish', 'mega-qurilish-' . uniqid());
        $supportUser = $this->setupUserWithRole('support', 'support@example.uz');
        $adminUser = $this->setupUserWithRole('admin', 'admin@example.uz');

        $payout = SellerPayout::create([
            'seller_id' => $seller->id,
            'amount' => 500000,
            'status' => 'requested',
            'bank_details' => ['bank_account' => '20208000900000123456'],
        ]);

        // 1. Support user attempts payout review -> 403 Forbidden
        $supportRes = $this->actingAs($supportUser, 'sanctum')->postJson("/api/v1/admin/payouts/{$payout->id}/review", [
            'status' => 'processing',
        ]);
        $supportRes->assertStatus(403);
        $this->assertContains($supportRes->json('error.code'), ['INSUFFICIENT_PAYOUT_PERMISSION', 'FORBIDDEN']);

        // 2. Admin user approves payout -> 200 OK
        $adminRes = $this->actingAs($adminUser, 'sanctum')->postJson("/api/v1/admin/payouts/{$payout->id}/review", [
            'status' => 'completed',
            'notes' => 'Mablag\' bank hisob raqamiga o\'tkazib berildi',
        ]);
        $adminRes->assertStatus(200)->assertJsonPath('data.status', 'completed');

        $this->assertEquals('completed', $payout->fresh()->status);
        $this->assertDatabaseHas('seller_ledgers', [
            'seller_id' => $seller->id,
            'type' => 'debit',
            'entry_type' => 'payout',
            'amount' => 500000,
        ]);
    }

    public function test_partial_shipment_does_not_complete_parent_order_early(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_ship@example.uz');
        $operator = $this->setupUserWithRole('logistics_operator', 'logist@example.uz');
        $courier = $this->setupUserWithRole('courier', 'courier@example.uz');

        $sellerA = $this->createSeller('Seller A Store', 'seller-a-ship-' . uniqid());
        $sellerB = $this->createSeller('Seller B Store', 'seller-b-ship-' . uniqid());

        // Create Parent Order with 2 Suborders
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-MULTI-' . uniqid(),
            'status' => 'processing',
            'currency' => 'UZS',
            'items_total' => 200000,
            'shipping_total' => 30000,
            'grand_total' => 230000,
            'shipping_address' => ['recipient_name' => 'Ali', 'phone' => '+998901234567', 'address' => 'Chilonzor'],
        ]);

        $suborderA = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $sellerA->id,
            'suborder_number' => 'SUB-A-' . uniqid(),
            'status' => 'shipped',
            'items_subtotal' => 100000,
            'shipping_cost' => 15000,
            'commission_rate' => 5.0,
            'commission_amount' => 5000,
            'total_amount' => 115000,
        ]);

        $suborderB = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $sellerB->id,
            'suborder_number' => 'SUB-B-' . uniqid(),
            'status' => 'shipped',
            'items_subtotal' => 100000,
            'shipping_cost' => 15000,
            'commission_rate' => 5.0,
            'commission_amount' => 5000,
            'total_amount' => 115000,
        ]);

        $shipmentA = Shipment::create([
            'suborder_id' => $suborderA->id,
            'tracking_number' => 'TRK-A-' . uniqid(),
            'courier_id' => $courier->id,
            'status' => 'in_transit',
        ]);

        $shipmentB = Shipment::create([
            'suborder_id' => $suborderB->id,
            'tracking_number' => 'TRK-B-' . uniqid(),
            'courier_id' => $courier->id,
            'status' => 'in_transit',
        ]);

        $transitionService = app(ShipmentTransitionService::class);

        // 1. Deliver shipment A only
        $transitionService->transitionStatus($shipmentA, 'delivered');

        // Suborder A is delivered
        $this->assertEquals('delivered', $suborderA->fresh()->status);
        // Suborder B is still shipped / in_transit
        $this->assertEquals('shipped', $suborderB->fresh()->status);
        // Parent order MUST remain 'processing' (NOT completed)
        $this->assertEquals('processing', $order->fresh()->status);

        // 2. Deliver shipment B
        $transitionService->transitionStatus($shipmentB, 'delivered');

        // Both delivered: Parent order NOW transitions to 'completed'
        $this->assertEquals('delivered', $suborderB->fresh()->status);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_ton_freight_shipping_blocks_payment_until_negotiated(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_ton@example.uz');
        $seller = $this->createSeller('Ohak Zavodi', 'ohak-zavod-' . uniqid());

        $category = Category::firstOrCreate(['slug' => 'ohak'], ['name' => 'Ohak', 'is_active' => true]);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Kalsiy Ohak',
            'slug' => 'kalsiy-ohak-' . uniqid(),
            'unit' => 'ton',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'wholesale_only',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $variant = $product->variants()->create([
            'sku' => 'OHAK-TON-' . uniqid(),
            'name' => '1 Tonna Ohak',
            'price' => 450000,
            'currency' => 'UZS',
        ]);

        // Order with ton item, but unnegotiated 0 UZS shipping
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-TON-' . uniqid(),
            'status' => 'pending_payment',
            'currency' => 'UZS',
            'items_total' => 4500000,
            'shipping_total' => 0, // Unnegotiated freight!
            'grand_total' => 4500000,
            'shipping_address' => ['recipient_name' => 'Zavod Boshlig\'i', 'phone' => '+998901234567', 'address' => 'Olmaliq'],
        ]);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'SUB-TON-' . uniqid(),
            'status' => 'pending',
            'items_subtotal' => 4500000,
            'shipping_cost' => 0,
            'commission_rate' => 5.0,
            'commission_amount' => 225000,
            'total_amount' => 4500000,
        ]);

        OrderItem::create([
            'suborder_id' => $suborder->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_sku' => $variant->sku,
            'unit' => 'ton',
            'quantity' => 10.0,
            'unit_price' => 450000,
            'subtotal' => 4500000,
        ]);

        // Attempting to initiate payment on unnegotiated ton cargo -> blocked
        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'TON_FREIGHT_SHIPPING_REQUIRED');
    }

    public function test_rfq_workflow_and_atomic_stock_recheck_on_quotation_acceptance(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_rfq@example.uz');
        $seller = $this->createSeller('Metal Pro', 'metal-pro-' . uniqid());
        $sellerOwner = $this->setupUserWithRole('seller_owner', 'metal_owner@example.uz', $seller->id);

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => 'Metal Asosiy Baza',
            'code' => 'WH-METAL-' . uniqid(),
            'address' => 'Sergeli sanoat zonasi',
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(['slug' => 'metallurgiya'], ['name' => 'Metallurgiya', 'is_active' => true]);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Armatura A500C 12mm',
            'slug' => 'armatura-12mm-' . uniqid(),
            'unit' => 'ton',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $variant = $product->variants()->create([
            'sku' => 'ARM-12MM-' . uniqid(),
            'name' => 'Armatura 12mm (Tonna)',
            'price' => 9500000,
            'currency' => 'UZS',
        ]);

        // Inventory balance: 5 tons available
        $balance = InventoryBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => 5.0,
            'safety_stock' => 0.5,
        ]);

        // 1. Customer creates RFQ for 10 tons (which is currently more than stock)
        $rfqRes = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/rfqs', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 10.0,
            'unit' => 'ton',
            'target_price' => 9000000,
            'notes' => 'Obyekt qurilishi uchun 10 tonna armatura kerak',
        ]);
        $rfqRes->assertStatus(201);
        $rfqId = $rfqRes->json('data.id');

        // 2. Seller submits quotation
        $quoteRes = $this->actingAs($sellerOwner, 'sanctum')->postJson("/api/v1/seller/rfqs/{$rfqId}/quote", [
            'unit_price' => 9100000,
            'shipping_cost' => 500000,
            'is_shipping_negotiated' => true,
            'expires_at' => now()->addDays(2)->toIso8601String(),
            'notes' => 'Kran bilan yetkazish kelishildi',
        ]);
        $quoteRes->assertStatus(201);
        $quoteId = $quoteRes->json('data.id');

        // 3. Customer attempts acceptance while warehouse only has 5 tons -> rejected with 422
        $failAcceptRes = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/rfqs/quotations/{$quoteId}/accept", [
            'shipping_address' => [
                'recipient_name' => 'Quruvchi MCHJ',
                'phone' => '+998901234567',
                'address' => 'Yunusobod 4-mavze',
            ],
        ]);
        $failAcceptRes->assertStatus(422)
            ->assertJsonPath('error.code', 'QUOTATION_ACCEPTANCE_FAILED');

        // 4. Warehouse receives new delivery (+15 tons -> 20 tons total)
        $balance->update(['quantity' => 20.0]);

        // 5. Customer accepts quotation -> succeeds and creates Order
        $successAcceptRes = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/rfqs/quotations/{$quoteId}/accept", [
            'shipping_address' => [
                'recipient_name' => 'Quruvchi MCHJ',
                'phone' => '+998901234567',
                'address' => 'Yunusobod 4-mavze',
            ],
        ]);
        $successAcceptRes->assertStatus(201)
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.grand_total', 91500000); // (9.1m * 10) + 500k ship

        $quotation = RfqQuotation::find($quoteId);
        $this->assertEquals('accepted', $quotation->status);
        $this->assertEquals('accepted', $quotation->rfq->status);
    }

    public function test_shipment_state_machine_validates_transitions_and_delay_reasons(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust_sm@example.uz');
        $operator = $this->setupUserWithRole('logistics_operator', 'disp@example.uz');
        $courier = $this->setupUserWithRole('courier', 'cour@example.uz');
        $seller = $this->createSeller('Do\'kon SM', 'dsm-' . uniqid());

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SM-' . uniqid(),
            'status' => 'processing',
            'currency' => 'UZS',
            'items_total' => 100000,
            'shipping_total' => 10000,
            'grand_total' => 110000,
            'shipping_address' => ['recipient_name' => 'Test', 'phone' => '+998901234567', 'address' => 'Test'],
        ]);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'SUB-SM-' . uniqid(),
            'status' => 'pending',
            'items_subtotal' => 100000,
            'shipping_cost' => 10000,
            'commission_rate' => 5.0,
            'commission_amount' => 5000,
            'total_amount' => 110000,
        ]);

        $shipment = Shipment::create([
            'suborder_id' => $suborder->id,
            'tracking_number' => 'TRK-SM-' . uniqid(),
            'status' => 'pending',
        ]);

        // 1. Assign courier
        $assignRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/assign", [
            'courier_id' => $courier->id,
        ]);
        $assignRes->assertStatus(200)->assertJsonPath('data.status', 'assigned');

        // 2. Pick up
        $pickupRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
            'status' => 'picked_up',
        ]);
        $pickupRes->assertStatus(200)->assertJsonPath('data.status', 'picked_up');

        // 3. Move to in_transit
        $transitRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
            'status' => 'in_transit',
        ]);
        $transitRes->assertStatus(200)->assertJsonPath('data.status', 'in_transit');

        // 4. Delayed WITHOUT reason -> rejected with 422
        $noReasonRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
            'status' => 'delayed',
        ]);
        $noReasonRes->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_SHIPMENT_TRANSITION');

        // 5. Delayed WITH reason -> succeeds
        $withReasonRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
            'status' => 'delayed',
            'delay_reason' => 'Tirbandlik sababli 30 daqiqa kechikish',
        ]);
        $withReasonRes->assertStatus(200)->assertJsonPath('data.status', 'delayed');

        // 6. Deliver
        $delivRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
            'status' => 'delivered',
        ]);
        $delivRes->assertStatus(200)->assertJsonPath('data.status', 'delivered');

        // 7. Attempt illegal transition from delivered back to pending -> rejected
        $illegalRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/logistics/shipments/{$shipment->id}/status", [
            'status' => 'pending',
        ]);
        $illegalRes->assertStatus(422);
    }

    public function test_seller_can_create_product_with_uploaded_image_and_tiered_pricing(): void
    {
        $seller = $this->createSeller('Stroy Express', 'stroy-express');
        $user = $this->setupUserWithRole('seller_owner', 'owner@stroy.uz', $seller->id);

        $category = Category::create([
            'name' => 'Qurilish mollari',
            'slug' => 'qurilish-mollari',
            'is_active' => true,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('test_product.jpg', 600, 600);

        $response = $this->actingAs($user)->post('/seller/products', [
            'name' => 'Gipsokarton Knauf 9.5mm',
            'category_id' => $category->id,
            'trade_mode' => 'both',
            'unit' => 'piece',
            'min_order_quantity' => 10,
            'unit_step' => 1,
            'items_per_box' => 1,
            'retail_price' => 50000,
            'tier_300_price' => 46000,
            'tier_1000_price' => 42000,
            'stock_quantity' => 500,
            'image_file' => $file,
            'description' => 'Sifatli Knauf gipsokarton plitasi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'seller_id' => $seller->id,
            'name' => 'Gipsokarton Knauf 9.5mm',
            'trade_mode' => 'both',
        ]);

        $product = Product::where('name', 'Gipsokarton Knauf 9.5mm')->first();
        $this->assertNotNull($product);
        $this->assertStringStartsWith('/images/products/', $product->image_url);

        // Verify tiered pricing created
        $variant = $product->variants()->first();
        $this->assertNotNull($variant);
        $this->assertCount(3, $variant->priceTiers);
    }
}
