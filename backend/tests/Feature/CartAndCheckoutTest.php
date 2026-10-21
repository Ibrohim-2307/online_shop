<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Checkout\InventoryReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(string $phone = '+998901112233'): User
    {
        $role = Role::firstOrCreate(['name' => 'customer'], [
            'label' => 'Xaridor',
        ]);

        $user = User::create([
            'name' => 'Aziz Xaridor',
            'email' => 'customer_' . uniqid() . '@example.uz',
            'phone' => $phone,
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    protected function setupSellerWithProduct(
        string $sellerName,
        string $sellerSlug,
        string $productName,
        string $productSlug,
        string $sku,
        int $stock = 1000
    ): array {
        $seller = Seller::create([
            'name' => $sellerName,
            'slug' => $sellerSlug,
            'legal_name' => $sellerName . ' MCHJ',
            'tax_id' => '30' . rand(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.9,
        ]);

        $category = Category::firstOrCreate(['slug' => 'qurilish'], [
            'name' => 'Qurilish',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => $productSlug,
            'unit' => 'piece',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'name' => $productName . ' Standart',
            'price' => 300000, // 300,000 UZS base
            'currency' => 'UZS',
        ]);

        // Tier: 1-299 units -> 300,000 UZS
        $variant->priceTiers()->create([
            'min_quantity' => 1.0,
            'max_quantity' => 299.0,
            'unit_price' => 300000,
            'currency' => 'UZS',
        ]);

        // Tier: 300-999 units -> 270,000 UZS
        $variant->priceTiers()->create([
            'min_quantity' => 300.0,
            'max_quantity' => 999.0,
            'unit_price' => 270000,
            'currency' => 'UZS',
        ]);

        // Tier: 1000+ units -> 250,000 UZS
        $variant->priceTiers()->create([
            'min_quantity' => 1000.0,
            'max_quantity' => null,
            'unit_price' => 250000,
            'currency' => 'UZS',
        ]);

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => $sellerName . ' Ombori',
            'is_active' => true,
        ]);

        $balance = InventoryBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => (float) $stock,
            'reserved_quantity' => 0.0,
        ]);

        return [$seller, $product, $variant, $warehouse, $balance];
    }

    public function test_two_parallel_checkouts_cannot_double_sell_last_sku(): void
    {
        // Setup product with exactly 5 units in stock
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'StroyTest', 'stroy-test', 'Limit Sement', 'limit-sement', 'LIMIT-SKU-1', 5
        );

        $customer1 = $this->createCustomer('+998901111111');
        $customer2 = $this->createCustomer('+998902222222');

        $payload = [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 5],
            ],
            'shipping_address' => [
                'recipient_name' => 'Alisher',
                'phone' => '+998901111111',
                'region' => 'Toshkent',
                'city' => 'Chilonzor',
                'address_line' => 'Bunyodkor shoh ko\'chasi 1-uy',
            ],
        ];

        // 1. Customer 1 checkouts all 5 units -> Succeeds!
        $response1 = $this->actingAs($customer1, 'sanctum')->postJson('/api/v1/checkout', $payload);
        $response1->assertStatus(201);
        $this->assertEquals(1, Order::count());

        // 2. Customer 2 immediately attempts to purchase 5 units of same SKU -> Fails!
        $response2 = $this->actingAs($customer2, 'sanctum')->postJson('/api/v1/checkout', $payload);
        $response2->assertStatus(422);
        $response2->assertJsonPath('error.code', 'CHECKOUT_ERROR');

        // Only 1 order exists in database
        $this->assertEquals(1, Order::count());
    }

    public function test_idempotency_key_replay_returns_same_order(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'StroyTest2', 'stroy-test-2', 'Gips', 'gips-test', 'GIPS-SKU-1', 100
        );

        $customer = $this->createCustomer();
        $idempotencyKey = 'IDEMPOTENCY-TEST-KEY-001';

        $payload = [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 10],
            ],
            'shipping_address' => [
                'recipient_name' => 'Farrux',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Yunusobod',
                'address_line' => 'Navoiy 14',
            ],
        ];

        // 1. Initial request creates order
        $resp1 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payload);

        $resp1->assertStatus(201);
        $orderId = $resp1->json('data.order_id');
        $orderNum = $resp1->json('data.order_number');

        // 2. Exact same request replayed with identical Idempotency-Key
        $resp2 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payload);

        $resp2->assertStatus(200);
        $this->assertTrue($resp2->json('data.is_replay'));
        $this->assertEquals($orderNum, $resp2->json('data.order_number'));

        // Strictly 1 order in database
        $this->assertEquals(1, Order::count());
    }

    public function test_idempotency_key_mismatch_payload_is_rejected(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'StroyTest3', 'stroy-test-3', 'Bo\'yoq', 'boyoq-test', 'BOYOQ-SKU-1', 100
        );

        $customer = $this->createCustomer();
        $idempotencyKey = 'IDEMPOTENCY-TEST-KEY-002';

        $payloadA = [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 2],
            ],
            'shipping_address' => [
                'recipient_name' => 'Farrux',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Yunusobod',
                'address_line' => 'Navoiy 14',
            ],
        ];

        $resp1 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payloadA);
        $resp1->assertStatus(201);

        // Payload B with different quantity under SAME Idempotency-Key
        $payloadB = $payloadA;
        $payloadB['items'][0]['quantity'] = 10;

        $resp2 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payloadB);

        $resp2->assertStatus(422);
        $resp2->assertJsonPath('error.code', 'IDEMPOTENCY_PAYLOAD_MISMATCH');
    }

    public function test_multi_seller_order_splits_into_suborders_with_individual_shipping(): void
    {
        // Seller 1: Cement
        [$seller1, $p1, $v1] = $this->setupSellerWithProduct(
            'Seller One', 'seller-one', 'Sement', 'sement-1', 'SEM-1', 500
        );
        // Seller 2: Generator
        [$seller2, $p2, $v2] = $this->setupSellerWithProduct(
            'Seller Two', 'seller-two', 'Generator', 'gen-1', 'GEN-1', 50
        );

        $customer = $this->createCustomer();

        $payload = [
            'items' => [
                ['variant_id' => $v1->id, 'quantity' => 2],
                ['variant_id' => $v2->id, 'quantity' => 1],
            ],
            'shipping_address' => [
                'recipient_name' => 'Akbar',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Yakkasaroy',
                'address_line' => 'Shota Rustaveli 25',
            ],
        ];

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/checkout', $payload);
        $response->assertStatus(201);

        $orderId = $response->json('data.order_id');
        $order = Order::with('suborders.items', 'suborders.shipments')->findOrFail($orderId);

        // 1 Parent order with 2 Suborders
        $this->assertCount(2, $order->suborders);

        $suborderSellers = $order->suborders->pluck('seller_id')->all();
        $this->assertContains($seller1->id, $suborderSellers);
        $this->assertContains($seller2->id, $suborderSellers);

        // Each suborder has its own shipment and tracking number
        foreach ($order->suborders as $suborder) {
            $this->assertNotEmpty($suborder->suborder_number);
            $this->assertCount(1, $suborder->shipments);
            $this->assertStringStartsWith('TRK-', $suborder->shipments->first()->tracking_number);
        }
    }

    public function test_tiered_pricing_recalculated_at_299_vs_300_boundary(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'TierSeller', 'tier-seller', 'AllUnits Test', 'all-units-test', 'TIER-SKU', 5000
        );

        $customer = $this->createCustomer();

        // 1. Checkout with 299 units (Base price = 300,000 UZS)
        // Expected subtotal: 299 * 300,000 = 89,700,000 UZS
        $payload299 = [
            'items' => [['variant_id' => $variant->id, 'quantity' => 299]],
            'shipping_address' => [
                'recipient_name' => 'Dilshod',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Mirobod',
                'address_line' => 'Nukus 80',
            ],
        ];

        $resp299 = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/checkout', $payload299);
        $resp299->assertStatus(201);
        $order299 = Order::findOrFail($resp299->json('data.order_id'));
        $this->assertEquals(89700000, $order299->items_total);

        // 2. Checkout with 300 units (Tier price = 270,000 UZS)
        // Expected subtotal: 300 * 270,000 = 81,000,000 UZS (< 89,700,000!)
        $payload300 = [
            'items' => [['variant_id' => $variant->id, 'quantity' => 300]],
            'shipping_address' => [
                'recipient_name' => 'Dilshod',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Mirobod',
                'address_line' => 'Nukus 80',
            ],
        ];

        $resp300 = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/checkout', $payload300);
        $resp300->assertStatus(201);
        $order300 = Order::findOrFail($resp300->json('data.order_id'));
        $this->assertEquals(81000000, $order300->items_total);
        $this->assertTrue($order300->items_total < $order299->items_total);
    }

    public function test_expired_reservation_releases_stock_back_to_available_balance(): void
    {
        [$seller, $product, $variant, $warehouse, $balance] = $this->setupSellerWithProduct(
            'ExpireSeller', 'expire-seller', 'Gips Blok', 'gips-blok', 'GIPS-EXP', 100
        );

        $reservationService = app(InventoryReservationService::class);

        // Reserve 20 units
        $res = $reservationService->reserveVariantStock($variant, 20);
        $this->assertEquals(20.0, (float) $balance->fresh()->reserved_quantity);

        // Force expiry in past
        $res->update(['expires_at' => now()->subMinutes(5)]);

        // Release expired
        $releasedCount = $reservationService->releaseExpiredReservations();
        $this->assertEquals(1, $releasedCount);

        // Reserved balance returned to 0
        $this->assertEquals(0.0, (float) $balance->fresh()->reserved_quantity);
        $this->assertEquals('expired', $res->fresh()->status);

        // Idempotency: second run does not release again
        $secondRun = $reservationService->releaseExpiredReservations();
        $this->assertEquals(0, $secondRun);
        $this->assertEquals(0.0, (float) $balance->fresh()->reserved_quantity);
    }

    public function test_web_cart_and_checkout_flow(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'WebSeller', 'web-seller', 'Armatura', 'armatura-web', 'ARM-WEB', 200
        );

        $customer = $this->createCustomer('+998909998877');

        // 1. Visit empty cart
        $cartResp = $this->actingAs($customer)->get('/cart');
        $cartResp->assertStatus(200);
        $cartResp->assertSee('Savatchangiz bo\'sh', false);

        // 2. Add item to cart via API
        $addResp = $this->actingAs($customer)->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $addResp->assertStatus(201);

        // 3. Visit checkout page
        $checkoutGet = $this->actingAs($customer)->get('/checkout');
        $checkoutGet->assertStatus(200);
        $checkoutGet->assertSee('Buyurtmani rasmiylashtirish');
        $checkoutGet->assertSee('WebSeller');

        // 4. Submit web checkout
        $checkoutPost = $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Botir Web',
            'phone' => '+998909998877',
            'region' => 'Toshkent shahri',
            'city' => 'Shayxontohur tumani',
            'address_line' => 'Ko\'kcha darvoza 12',
            'notes' => 'Qo\'ng\'iroq qilib keling',
        ]);

        $checkoutPost->assertSessionHasNoErrors();
        $checkoutPost->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Botir Web', $order->shipping_address['recipient_name']);

        // 5. Follow redirect to order success page
        $successResp = $this->actingAs($customer)->get('/orders/' . $order->order_number . '/success');
        $successResp->assertStatus(200);
        $successResp->assertSee($order->order_number);
        $successResp->assertSee('Buyurtmangiz qabul qilindi!');
    }

    public function test_guest_can_add_item_to_cart_via_web_and_view_it(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'B2B Seller', 'b2b-seller', 'Gips', 'gips-test', 'GIPS-01', 500
        );

        // 1. Post to web /cart/items as guest
        $response = $this->postJson('/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 2. Visit /cart - it should show the item and NOT be empty!
        $cartResp = $this->get('/cart');
        $cartResp->assertStatus(200);
        $cartResp->assertDontSee('Savatchangiz hozircha bo\'sh', false);
        $cartResp->assertSee('Gips');
    }
}
