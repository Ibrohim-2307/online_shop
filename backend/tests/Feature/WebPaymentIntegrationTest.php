<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebPaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Seller $seller;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'customer'], ['label' => 'Xaridor']);

        $this->customer = User::create([
            'name' => 'Dilshod Xaridor',
            'email' => 'dilshod@example.uz',
            'phone' => '+998901234567',
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);
        $this->customer->roles()->attach($role->id);

        $this->seller = Seller::create([
            'slug' => 'test-seller',
            'name' => 'Stroy Optom',
            'legal_name' => 'Stroy Optom MCHJ',
            'tax_id' => '301234567',
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 5.0,
        ]);

        $category = Category::create([
            'slug' => 'qurilish-mat',
            'name' => 'Qurilish Materiallari',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'slug' => 'sement-50kg',
            'name' => 'Sement 50kg',
            'unit' => 'piece',
            'unit_step' => 1.0,
            'min_order_quantity' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SEM-50',
            'name' => '50kg',
            'is_active' => true,
        ]);

        PriceTier::create([
            'product_variant_id' => $this->variant->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'unit_price' => 50000,
        ]);

        $wh = Warehouse::create([
            'seller_id' => $this->seller->id,
            'name' => 'Ombor 1',
            'code' => 'WH-1',
            'is_active' => true,
        ]);

        InventoryBalance::create([
            'warehouse_id' => $wh->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 100.0,
            'reserved_quantity' => 0.0,
        ]);
    }

    public function test_checkout_with_cash_on_delivery(): void
    {
        // 1. Add item to cart
        $this->actingAs($this->customer)->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 2,
        ])->assertStatus(201);

        // 2. Process checkout with cash on delivery
        $response = $this->actingAs($this->customer)->post('/checkout', [
            'recipient_name' => 'Dilshod Xaridor',
            'phone' => '+998901234567',
            'region' => 'Toshkent shahri',
            'city' => 'Chilonzor',
            'address_line' => 'Bunyodkor shoh ko\'chasi 15-uy',
            'payment_method' => 'cash_on_delivery',
            'idempotency_key' => (string) Str::uuid(),
        ]);


        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('processing', $order->status);

        $response->assertRedirect(route('order.success', ['orderNumber' => $order->order_number]));

        // Verify Payment record
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('cash_on_delivery', $payment->payment_method);
        $this->assertEquals('pending', $payment->status);

        // Verify Success page shows COD badge
        $successPage = $this->actingAs($this->customer)->get(route('order.success', ['orderNumber' => $order->order_number]));
        $successPage->assertStatus(200);
        $successPage->assertSee('Yetkazib berishda to\'lanadi', false);
    }

    public function test_checkout_with_click_and_instant_settlement(): void
    {
        // 1. Add item to cart
        $this->actingAs($this->customer)->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 1,
        ])->assertStatus(201);

        // 2. Process checkout with Click
        $response = $this->actingAs($this->customer)->post('/checkout', [
            'recipient_name' => 'Dilshod Xaridor',
            'phone' => '+998901234567',
            'region' => 'Toshkent shahri',
            'city' => 'Chilonzor',
            'address_line' => 'Bunyodkor shoh ko\'chasi 15-uy',
            'payment_method' => 'click',
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);

        // Redirects directly to payment checkout page
        $response->assertRedirect(route('payment.checkout', ['orderNumber' => $order->order_number]));

        // 3. Open Payment Checkout View
        $checkoutView = $this->actingAs($this->customer)->get(route('payment.checkout', ['orderNumber' => $order->order_number]));
        $checkoutView->assertStatus(200);
        $checkoutView->assertSee('Click');
        $checkoutView->assertSee('Payme');
        $checkoutView->assertSee('Onlayn To\'lov Oynasi', false);

        // 4. Settle payment via Demo Sandbox
        $payResponse = $this->actingAs($this->customer)->post(route('payment.pay', ['orderNumber' => $order->order_number]), [
            'payment_method' => 'demo',
        ]);

        $payResponse->assertRedirect(route('order.success', ['orderNumber' => $order->order_number]));

        // Verify order is now processing and payment is paid
        $order->refresh();
        $this->assertEquals('processing', $order->status);

        $payment = Payment::where('order_id', $order->id)->latest()->first();
        $this->assertEquals('paid', $payment->status);

        // Verify Success page shows Paid confirmation
        $successPage = $this->actingAs($this->customer)->get(route('order.success', ['orderNumber' => $order->order_number]));
        $successPage->assertStatus(200);
        $successPage->assertSee('To\'lov to\'liq qabul qilindi', false);
    }
}
