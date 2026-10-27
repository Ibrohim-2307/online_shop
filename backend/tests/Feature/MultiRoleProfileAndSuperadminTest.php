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
use App\Models\SellerMember;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiRoleProfileAndSuperadminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_customer_sees_standard_profile(): void
    {
        $customerRole = Role::where('name', 'customer')->first();
        $customer = User::factory()->create();
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)->get(route('profile'));

        $response->assertStatus(200);
        $response->assertSee('Mening Buyurtmalarim');
        $response->assertSee('Sevimlilar & Saqlangan', false);
        $response->assertSee('Yetkazish Manzillari');
        // Standard customer should not have seller section
        $response->assertDontSee("SOTUVCHI BO'LIMI", false);
        $response->assertDontSee("KURYER BO'LIMI", false);
    }

    public function test_seller_sees_seller_section_and_can_add_product_from_profile(): void
    {
        $sellerRole = Role::where('name', 'seller_owner')->first();
        $user = User::factory()->create(['name' => 'Sotuvchi Akmal']);
        $user->roles()->attach($sellerRole);

        $seller = Seller::create([
            'name' => 'Akmal Stroy',
            'slug' => 'akmal-stroy',
            'legal_name' => 'Akmal Stroy XK',
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 5.0,
            'phone' => '+998901112233',
        ]);
        SellerMember::create(['seller_id' => $seller->id, 'user_id' => $user->id, 'role' => 'owner', 'is_active' => true]);
        $user->update(['seller_id' => $seller->id]);

        $category = Category::create([
            'name' => 'Qurilish Materiallari',
            'slug' => 'qurilish-materiallari',
            'is_active' => true,
        ]);

        // Visit profile
        $response = $this->actingAs($user)->get(route('profile', ['tab' => 'seller_products']));
        $response->assertStatus(200);
        $response->assertSee("SOTUVCHI BO'LIMI", false);
        $response->assertSee("Mahsulotlarim");
        $response->assertSee("Kelgan Buyurtmalar");

        // Add a product from profile
        $postRes = $this->actingAs($user)->post(route('profile.seller.products.store'), [
            'name' => 'Armatura A500C 12mm',
            'category_id' => $category->id,
            'unit' => 'ton',
            'trade_mode' => 'wholesale_only',
            'price' => 8500000,
            'stock_quantity' => 25,
            'min_order_quantity' => 1,
            'description' => 'Sifatli po\'lat armatura',
        ]);

        $postRes->assertRedirect(route('profile', ['tab' => 'seller_products']));
        $this->assertDatabaseHas('products', [
            'name' => 'Armatura A500C 12mm',
            'seller_id' => $seller->id,
            'unit' => 'ton',
            'trade_mode' => 'wholesale_only',
        ]);

        $product = Product::where('name', 'Armatura A500C 12mm')->first();
        $this->assertNotNull($product);
        $this->assertDatabaseHas('price_tiers', [
            'unit_price' => 8500000,
        ]);

        // Toggle active status
        $toggleRes = $this->actingAs($user)->post(route('profile.seller.products.status', $product->id), [
            'toggle_active' => 1,
        ]);
        $toggleRes->assertRedirect();
        $product->refresh();
        $this->assertFalse((bool)$product->is_active);
    }

    public function test_courier_sees_deliveries_and_completes_shipment_with_cod(): void
    {
        $courierRole = Role::where('name', 'courier')->first();
        $courier = User::factory()->create(['name' => 'Jasur Kuryer', 'phone' => '+998909998877']);
        $courier->roles()->attach($courierRole);

        $customer = User::factory()->create(['name' => 'Dilshod Xaridor', 'phone' => '+998901234567']);
        $seller = Seller::create([
            'name' => 'Metall Baza',
            'slug' => 'metall-baza',
            'status' => 'active',
            'phone' => '+998901110022',
            'address' => 'Toshkent sh., Chilonzor 10',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-999',
            'user_id' => $customer->id,
            'status' => 'processing',
            'grand_total' => 250000,
            'items_total' => 230000,
            'shipping_total' => 20000,
            'shipping_address' => ['recipient_name' => 'Dilshod', 'address_line' => 'Yunusobod 4-mavze', 'phone' => '+998901234567'],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'amount' => 250000,
            'currency' => 'UZS',
            'status' => 'pending',
        ]);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'SUB-999-1',
            'status' => 'confirmed',
            'items_subtotal' => 230000,
            'total_amount' => 250000,
            'shipping_cost' => 20000,
        ]);

        $shipment = Shipment::create([
            'suborder_id' => $suborder->id,
            'tracking_number' => 'TRK-999-COURIER',
            'courier_id' => $courier->id,
            'status' => 'assigned',
            'shipping_method' => 'courier_express',
            'pickup_address' => ['store_name' => $seller->name, 'address' => $seller->address, 'phone' => $seller->phone],
            'delivery_address' => $order->shipping_address,
        ]);

        // Courier views profile
        $response = $this->actingAs($courier)->get(route('profile', ['tab' => 'courier_shipments']));
        $response->assertStatus(200);
        $response->assertSee("KURYER BO'LIMI", false);
        $response->assertSee("TRK-999-COURIER");
        $response->assertSee("Metall Baza");
        $response->assertSee("Dilshod");
        $response->assertSee("Naqd Pul (COD)");

        // 1. Courier picks up package
        $pickRes = $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), [
            'status' => 'picked_up',
        ]);
        $pickRes->assertRedirect();
        $shipment->refresh();
        $this->assertEquals('picked_up', $shipment->status);

        // 2. Courier starts transit
        $transitRes = $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), [
            'status' => 'in_transit',
        ]);
        $transitRes->assertRedirect();
        $shipment->refresh();
        $this->assertEquals('in_transit', $shipment->status);

        // 3. Courier delivers and collects COD
        $deliverRes = $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), [
            'status' => 'delivered',
        ]);
        $deliverRes->assertRedirect();
        $shipment->refresh();
        $this->assertEquals('delivered', $shipment->status);
        $this->assertNotNull($shipment->delivered_at);

        // Verify COD payment is marked paid
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals($courier->id, $payment->payload_snapshot['collected_by_courier_id']);
    }

    public function test_superadmin_can_promote_user_and_assign_courier(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();
        $superadmin = User::factory()->create(['name' => 'Boshqaruvchi Superadmin']);
        $superadmin->roles()->attach($superadminRole);

        $targetUser = User::factory()->create(['name' => 'Yangi Sotuvchi']);
        $customerRole = Role::where('name', 'customer')->first();
        $targetUser->roles()->attach($customerRole);

        // Superadmin opens /superadmin and /admin
        $adminRes = $this->actingAs($superadmin)->get(route('admin.dashboard'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee("Superadmin");

        $superadminRes = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));
        $superadminRes->assertStatus(200);

        // Promote user to seller_owner
        $roleRes = $this->actingAs($superadmin)->post(route('admin.users.role', $targetUser->id), [
            'role' => 'seller_owner',
        ]);
        $roleRes->assertRedirect();

        $targetUser->refresh();
        $this->assertTrue($targetUser->isSeller());
        $this->assertNotNull($targetUser->seller_id);

        // Assign courier to shipment
        $courierRole = Role::where('name', 'courier')->first();
        $courier = User::factory()->create(['name' => 'Kuryer Shaxzod']);
        $courier->roles()->attach($courierRole);

        $order = Order::create([
            'order_number' => 'ORD-ASSIGN-1',
            'user_id' => $targetUser->id,
            'status' => 'processing',
            'grand_total' => 100000,
            'items_total' => 100000,
            'shipping_address' => ['recipient_name' => 'Xaridor', 'address_line' => 'Toshkent sh.'],
        ]);
        $seller = Seller::find($targetUser->seller_id);
        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'SUB-ASSIGN-1',
            'status' => 'confirmed',
            'items_subtotal' => 100000,
            'total_amount' => 100000,
        ]);
        $shipment = Shipment::create([
            'suborder_id' => $suborder->id,
            'tracking_number' => 'TRK-ASSIGN-1',
            'status' => 'pending',
            'shipping_method' => 'courier_express',
        ]);

        $assignRes = $this->actingAs($superadmin)->post(route('admin.shipments.assign', $shipment->id), [
            'courier_id' => $courier->id,
        ]);
        $assignRes->assertRedirect();
        $shipment->refresh();
        $this->assertEquals($courier->id, $shipment->courier_id);
        $this->assertEquals('assigned', $shipment->status);
    }
}
