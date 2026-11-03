<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealStoreReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_register_page_renders_and_customer_can_sign_up(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee("Ro'yxatdan o'tish", false);

        $response = $this->post('/register', [
            'account_type' => 'customer',
            'name' => 'Dilnoza Karimova',
            'phone' => '+998 90 123 45 67',
            'password' => 'Parol12345',
            'password_confirmation' => 'Parol12345',
            'terms' => '1',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticated();

        $user = User::where('phone', '+998901234567')->firstOrFail();
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_seller_sign_up_creates_pending_store(): void
    {
        $this->post('/register', [
            'account_type' => 'seller',
            'name' => 'Sardor Do\'konchi',
            'phone' => '+998935554433',
            'store_name' => 'Sardor Savdo',
            'password' => 'Parol12345',
            'password_confirmation' => 'Parol12345',
            'terms' => '1',
        ])->assertRedirect('/profile?tab=seller_products');

        $user = User::where('phone', '+998935554433')->firstOrFail();
        $this->assertTrue($user->hasRole('seller_owner'));
        $this->assertDatabaseHas('sellers', ['name' => 'Sardor Savdo', 'status' => 'pending']);
    }

    public function test_registration_rejects_role_escalation_and_duplicates(): void
    {
        User::create(['name' => 'Bor', 'email' => 'bor@example.uz', 'phone' => '+998901111111', 'password' => 'x12345678', 'is_active' => true]);

        $this->post('/register', [
            'account_type' => 'admin',
            'name' => 'Hacker',
            'phone' => '+998901111111',
            'password' => 'Parol12345',
            'password_confirmation' => 'Parol12345',
            'terms' => '1',
        ])->assertSessionHasErrors(['account_type', 'phone']);

        $this->assertGuest();
    }

    public function test_catalog_sorts_by_real_retail_price(): void
    {
        $seller = Seller::create(['name' => 'S', 'slug' => 's', 'status' => 'active']);
        $category = Category::create(['name' => 'C', 'slug' => 'c', 'is_active' => true]);

        foreach (['Qimmat' => 900000, 'Arzon' => 1000, 'Orta' => 50000] as $name => $price) {
            $product = Product::create([
                'seller_id' => $seller->id, 'category_id' => $category->id, 'name' => $name,
                'slug' => strtolower($name), 'unit' => 'piece', 'trade_mode' => 'both',
                'status' => 'approved', 'is_active' => true,
            ]);
            $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'SKU-' . $name, 'name' => $name, 'is_active' => true]);
            $variant->priceTiers()->create(['min_quantity' => 1, 'max_quantity' => 9, 'unit_price' => $price, 'currency' => 'UZS']);
            // A cheaper bulk tier must not distort the retail price used for sorting
            $variant->priceTiers()->create(['min_quantity' => 10, 'max_quantity' => null, 'unit_price' => (int) ($price * 0.5), 'currency' => 'UZS']);
        }

        $this->get('/catalog?sort=price_asc')->assertStatus(200)->assertSeeInOrder(['Arzon', 'Orta', 'Qimmat']);
        $this->get('/catalog?sort=price_desc')->assertStatus(200)->assertSeeInOrder(['Qimmat', 'Orta', 'Arzon']);

        $filtered = $this->get('/catalog?min_price=10000&max_price=100000');
        $filtered->assertStatus(200)->assertSee('Orta')->assertDontSee('>Arzon<', false)->assertDontSee('>Qimmat<', false);
    }

    public function test_tracking_hides_personal_data_from_strangers_and_lists_nothing_without_number(): void
    {
        $customer = User::create(['name' => 'Owner', 'email' => 'own@example.uz', 'phone' => '+998901230000', 'password' => 'x12345678', 'is_active' => true]);
        $seller = Seller::create(['name' => 'S2', 'slug' => 's2', 'status' => 'active']);

        $order = Order::create([
            'order_number' => 'ORD-PRIV-1', 'user_id' => $customer->id, 'status' => 'processing', 'currency' => 'UZS',
            'items_total' => 1000, 'shipping_total' => 0, 'grand_total' => 1000,
            'shipping_address' => ['recipient_name' => 'Owner Person', 'phone' => '+998901230000', 'region' => 'Toshkent', 'city' => 'Chilonzor', 'address_line' => 'Maxfiy ko\'cha 7'],
        ]);
        $suborder = Suborder::create([
            'order_id' => $order->id, 'seller_id' => $seller->id, 'suborder_number' => 'SUB-PRIV-1', 'status' => 'confirmed',
            'items_subtotal' => 1000, 'shipping_cost' => 0, 'total_amount' => 1000, 'commission_rate' => 5, 'commission_amount' => 50,
        ]);
        Shipment::create([
            'suborder_id' => $suborder->id, 'tracking_number' => 'TRK-PRIV-1', 'status' => 'pending', 'shipping_method' => 'standard',
            'shipping_cost' => 0, 'delivery_address' => $order->shipping_address,
        ]);

        // Stranger with the number: progress visible, address/phone hidden
        $this->get('/tracking/TRK-PRIV-1')->assertStatus(200)
            ->assertDontSee('Maxfiy ko')
            ->assertDontSee('+998901230000');

        // Owner sees full details
        $this->actingAs($customer)->get('/tracking/TRK-PRIV-1')->assertStatus(200)
            ->assertSee("Maxfiy ko'cha 7");

        // Without a number, an anonymous visitor sees no one's shipment
        $this->app['auth']->forgetGuards();
        $this->get('/tracking')->assertStatus(200)->assertDontSee('TRK-PRIV-1');
    }
}
