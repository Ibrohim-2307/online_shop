<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealPaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Seller $seller;
    protected ProductVariant $variant;
    protected Warehouse $warehouse;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.click.service_id' => '12345',
            'services.click.merchant_id' => '67890',
            'services.click.secret_key' => 'click_secret_key_test_123',
            'services.payme.merchant_id' => 'payme_merchant_id_test',
            'services.payme.secret_key' => 'payme_secret_key_test_123',
            'services.payment.allow_demo_settlement' => false,
        ]);

        $role = Role::firstOrCreate(['name' => 'customer'], ['label' => 'Xaridor']);

        $this->customer = User::create([
            'name' => 'Nodir Xaridor',
            'email' => 'nodir@example.uz',
            'phone' => '+998909998877',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);
        $this->customer->roles()->attach($role->id);

        $this->seller = Seller::create([
            'slug' => 'real-seller',
            'name' => 'Mega Market Seller',
            'legal_name' => 'Mega Market MCHJ',
            'tax_id' => '309999999',
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.9,
        ]);

        $category = Category::create([
            'slug' => 'texnika',
            'name' => 'Elektronika va Texnika',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'slug' => 'test-smart-gadget',
            'name' => 'Smart Gadget Pro',
            'unit' => 'piece',
            'trade_mode' => 'both',
            'min_order_quantity' => 1,
            'unit_step' => 1,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-SMART-PRO',
            'name' => 'Default Variant',
            'status' => 'active',
        ]);

        PriceTier::create([
            'product_variant_id' => $this->variant->id,
            'min_quantity' => 1,
            'unit_price' => 500000,
        ]);

        $this->warehouse = Warehouse::create([
            'seller_id' => $this->seller->id,
            'name' => 'Toshkent Asosiy Ombor',
            'address' => 'Toshkent sh., Chilonzor',
            'is_active' => true,
        ]);

        InventoryBalance::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 100,
            'reserved_quantity' => 1,
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-TEST-' . date('Ymd') . '-001',
            'user_id' => $this->customer->id,
            'status' => 'pending_payment',
            'currency' => 'UZS',
            'items_total' => 500000,
            'shipping_total' => 20000,
            'discount_total' => 0,
            'tax_total' => 0,
            'grand_total' => 520000,
            'shipping_address' => [
                'recipient_name' => 'Nodir Xaridor',
                'phone' => '+998909998877',
                'city' => 'Toshkent',
                'address_line' => 'Amir Temur ko\'chasi, 12-uy',
            ],
        ]);

        Suborder::create([
            'order_id' => $this->order->id,
            'seller_id' => $this->seller->id,
            'suborder_number' => $this->order->order_number . '-S1',
            'status' => 'pending',
            'items_subtotal' => 500000,
            'shipping_cost' => 20000,
            'total_amount' => 520000,
            'commission_rate' => 5.0,
            'commission_amount' => 25000,
        ]);

        InventoryReservation::create([
            'order_id' => $this->order->id,
            'product_variant_id' => $this->variant->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'status' => 'reserved',
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    /**
     * Test Click Prepare endpoint: signature check fails if secret or hash does not match.
     */
    public function test_click_prepare_rejects_invalid_signature(): void
    {
        $payload = [
            'click_trans_id' => '1001',
            'service_id' => '12345',
            'click_paydoc_id' => '2001',
            'merchant_trans_id' => (string) $this->order->id,
            'amount' => '520000',
            'action' => '0',
            'error' => '0',
            'error_note' => '',
            'sign_time' => '2026-10-26 12:00:00',
            'sign_string' => 'invalid_md5_hash_string',
        ];

        $response = $this->postJson(route('api.v1.payments.click'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => -1,
            'error_note' => 'SIGN CHECK FAILED',
        ]);
    }

    /**
     * Test Click Prepare endpoint: succeeds with valid MD5 signature and matching order amount.
     */
    public function test_click_prepare_accepts_valid_signature_and_amount(): void
    {
        $clickTransId = '1002';
        $serviceId = '12345';
        $secretKey = 'click_secret_key_test_123';
        $merchantTransId = (string) $this->order->id;
        $amount = '520000.00';
        $action = '0'; // Prepare
        $signTime = '2026-10-26 12:00:00';

        $signString = md5($clickTransId . $serviceId . $secretKey . $merchantTransId . $amount . $action . $signTime);

        $payload = [
            'click_trans_id' => $clickTransId,
            'service_id' => $serviceId,
            'click_paydoc_id' => '2002',
            'merchant_trans_id' => $merchantTransId,
            'amount' => $amount,
            'action' => $action,
            'error' => '0',
            'error_note' => '',
            'sign_time' => $signTime,
            'sign_string' => $signString,
        ];

        $response = $this->postJson(route('api.v1.payments.click'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'click_trans_id' => (int) $clickTransId,
            'merchant_trans_id' => $merchantTransId,
            'error' => 0,
            'error_note' => 'Success',
        ]);

        $this->assertDatabaseHas('click_transactions', [
            'click_trans_id' => $clickTransId,
            'order_id' => $this->order->id,
            'action' => 0,
            'status' => 'prepared',
        ]);
    }

    /**
     * Test Click Complete endpoint: completes payment, updates order status to processing, and is idempotent.
     */
    public function test_click_complete_settles_order_and_is_idempotent(): void
    {
        $clickTransId = '1003';
        $serviceId = '12345';
        $secretKey = 'click_secret_key_test_123';
        $merchantTransId = (string) $this->order->id;
        $amount = '520000.00';
        $signTime = '2026-10-26 12:05:00';

        // 1. Prepare
        $prepSign = md5($clickTransId . $serviceId . $secretKey . $merchantTransId . $amount . '0' . $signTime);
        $prepRes = $this->postJson(route('api.v1.payments.click'), [
            'click_trans_id' => $clickTransId,
            'service_id' => $serviceId,
            'click_paydoc_id' => '2003',
            'merchant_trans_id' => $merchantTransId,
            'amount' => $amount,
            'action' => '0',
            'error' => '0',
            'error_note' => '',
            'sign_time' => $signTime,
            'sign_string' => $prepSign,
        ]);
        $prepRes->assertJson(['error' => 0]);
        $merchantPrepareId = (string) $prepRes->json('merchant_prepare_id');

        // 2. Complete (action = 1)
        $compSign = md5($clickTransId . $serviceId . $secretKey . $merchantTransId . $merchantPrepareId . $amount . '1' . $signTime);
        $compResponse = $this->postJson(route('api.v1.payments.click'), [
            'click_trans_id' => $clickTransId,
            'service_id' => $serviceId,
            'click_paydoc_id' => '2003',
            'merchant_trans_id' => $merchantTransId,
            'merchant_prepare_id' => $merchantPrepareId,
            'amount' => $amount,
            'action' => '1',
            'error' => '0',
            'error_note' => '',
            'sign_time' => $signTime,
            'sign_string' => $compSign,
        ]);

        $compResponse->assertStatus(200);
        $compResponse->assertJson([
            'click_trans_id' => (int) $clickTransId,
            'merchant_trans_id' => $merchantTransId,
            'error' => 0,
        ]);

        // Verify order and payment status
        $this->order->refresh();
        $this->assertEquals('processing', $this->order->status);

        $payment = Payment::where('order_id', $this->order->id)->latest()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('click', $payment->payment_method);

        // 3. Repeat callback (idempotency check)
        $repeatResponse = $this->postJson(route('api.v1.payments.click'), [
            'click_trans_id' => $clickTransId,
            'service_id' => $serviceId,
            'click_paydoc_id' => '2003',
            'merchant_trans_id' => $merchantTransId,
            'merchant_prepare_id' => $merchantTransId,
            'amount' => $amount,
            'action' => '1',
            'error' => '0',
            'error_note' => '',
            'sign_time' => $signTime,
            'sign_string' => $compSign,
        ]);

        $repeatResponse->assertStatus(200);
        $repeatResponse->assertJson(['error' => 0]);
    }

    /**
     * Test Payme JSON-RPC 2.0 CheckPerformTransaction.
     */
    public function test_payme_check_perform_transaction(): void
    {
        $amountInTiyin = 520000 * 100;
        $authHeader = 'Basic ' . base64_encode('Paycom:payme_secret_key_test_123');

        $response = $this->withHeaders([
            'Authorization' => $authHeader,
        ])->postJson(route('api.v1.payments.payme'), [
            'id' => 123,
            'method' => 'CheckPerformTransaction',
            'params' => [
                'amount' => $amountInTiyin,
                'account' => [
                    'order_id' => $this->order->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => 123,
            'result' => [
                'allow' => true,
            ],
        ]);
    }

    /**
     * Test Payme JSON-RPC: unauthorized request rejected with error code -32504.
     */
    public function test_payme_rejects_unauthorized_request(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('Paycom:wrong_secret_key'),
        ])->postJson(route('api.v1.payments.payme'), [
            'id' => 124,
            'method' => 'CheckPerformTransaction',
            'params' => [
                'amount' => 52000000,
                'account' => ['order_id' => $this->order->id],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => 124,
            'error' => [
                'code' => -32504,
            ],
        ]);
    }

    /**
     * Test Payme CreateTransaction and PerformTransaction flow.
     */
    public function test_payme_create_and_perform_transaction(): void
    {
        $amountInTiyin = 520000 * 100;
        $authHeader = 'Basic ' . base64_encode('Paycom:payme_secret_key_test_123');
        $paymeTransId = 'payme_trans_55555';
        $paymeTime = (int) (microtime(true) * 1000);

        // 1. CreateTransaction
        $createRes = $this->withHeaders(['Authorization' => $authHeader])->postJson(route('api.v1.payments.payme'), [
            'id' => 125,
            'method' => 'CreateTransaction',
            'params' => [
                'id' => $paymeTransId,
                'time' => $paymeTime,
                'amount' => $amountInTiyin,
                'account' => ['order_id' => $this->order->id],
            ],
        ]);

        $createRes->assertStatus(200);
        $createRes->assertJson([
            'id' => 125,
            'result' => [
                'state' => 1,
                'transaction' => (string) $this->order->id,
            ],
        ]);

        // 2. PerformTransaction
        $performRes = $this->withHeaders(['Authorization' => $authHeader])->postJson(route('api.v1.payments.payme'), [
            'id' => 126,
            'method' => 'PerformTransaction',
            'params' => [
                'id' => $paymeTransId,
            ],
        ]);

        $performRes->assertStatus(200);
        $performRes->assertJson([
            'id' => 126,
            'result' => [
                'state' => 2,
                'transaction' => (string) $this->order->id,
            ],
        ]);

        $this->order->refresh();
        $this->assertEquals('processing', $this->order->status);
    }

    /**
     * Test Wishlist Toggle & Sync endpoints.
     */
    public function test_wishlist_toggle_and_sync(): void
    {
        $product = $this->variant->product;

        // Toggle as authenticated user
        $response = $this->actingAs($this->customer)
            ->postJson(route('wishlist.toggle', ['productId' => $product->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'added',
            'count' => 1,
        ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);

        // Toggle again to remove
        $responseRemove = $this->actingAs($this->customer)
            ->postJson(route('wishlist.toggle', ['productId' => $product->id]));

        $responseRemove->assertJson([
            'success' => true,
            'status' => 'removed',
            'count' => 0,
        ]);

        // Sync array of IDs
        $syncRes = $this->actingAs($this->customer)
            ->postJson(route('wishlist.sync'), [
                'product_ids' => [$product->id],
            ]);

        $syncRes->assertStatus(200);
        $syncRes->assertJson([
            'success' => true,
            'count' => 1,
        ]);
    }

    /**
     * Test Customer Service & Legal Pages return HTTP 200.
     */
    public function test_customer_service_pages_are_accessible(): void
    {
        $this->get(route('delivery'))->assertStatus(200);
        $this->get(route('returns'))->assertStatus(200);
        $this->get(route('privacy'))->assertStatus(200);
        $this->get(route('contact'))->assertStatus(200);
    }

    /**
     * Test Expired Inventory Reservations Command.
     */
    public function test_inventory_release_expired_command(): void
    {
        // Create an expired reservation
        $expiredRes = InventoryReservation::create([
            'order_id' => $this->order->id,
            'product_variant_id' => $this->variant->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 2,
            'status' => 'reserved',
            'expires_at' => now()->subMinutes(30),
        ]);

        $this->warehouse->balances()->where('product_variant_id', $this->variant->id)->update([
            'reserved_quantity' => 3,
        ]);

        $this->artisan('inventory:release-expired')
            ->assertSuccessful();

        $expiredRes->refresh();
        $this->assertEquals('expired', $expiredRes->status);

        $balance = $this->warehouse->balances()->where('product_variant_id', $this->variant->id)->first();
        $this->assertEquals('1.000', $balance->reserved_quantity);
    }
}
