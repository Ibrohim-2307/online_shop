<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProfileWebTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(string $email = 'cust_prof@example.uz'): User
    {
        $role = Role::firstOrCreate(['name' => 'customer'], ['label' => 'Xaridor']);

        $user = User::create([
            'name' => 'Test Xaridor',
            'email' => $email,
            'phone' => '+998901234567',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    public function test_customer_profile_renders_for_authenticated_user(): void
    {
        $customer = $this->createCustomer();

        $res = $this->actingAs($customer)->get('/profile');
        $res->assertStatus(200)
            ->assertSee('Test Xaridor')
            ->assertSee('Mening Buyurtmalarim')
            ->assertSee('Yetkazish Manzillari')
            ->assertSee('Hisob Sozlamalari');
    }

    public function test_customer_can_update_profile_info(): void
    {
        $customer = $this->createCustomer('update_me@example.uz');

        $res = $this->actingAs($customer)->post('/profile', [
            'name' => 'Yangi Ism',
            'phone' => '+998939998877',
            'email' => 'updated_email@example.uz',
        ]);

        $res->assertRedirect();
        $customer->refresh();

        $this->assertEquals('Yangi Ism', $customer->name);
        $this->assertEquals('+998939998877', $customer->phone);
        $this->assertEquals('updated_email@example.uz', $customer->email);
    }

    public function test_customer_can_update_password(): void
    {
        $customer = $this->createCustomer('pwd_user@example.uz');

        $res = $this->actingAs($customer)->post('/profile/password', [
            'current_password' => 'Secret123!',
            'new_password' => 'NewPassword999!',
            'new_password_confirmation' => 'NewPassword999!',
        ]);

        $res->assertRedirect();
        $customer->refresh();

        $this->assertTrue(Hash::check('NewPassword999!', $customer->password));
    }

    public function test_customer_can_add_and_delete_delivery_address(): void
    {
        $customer = $this->createCustomer('addr_user@example.uz');

        $res = $this->actingAs($customer)->post('/profile/addresses', [
            'title' => 'Shahar Xonadoni',
            'recipient_name' => 'Azizbek',
            'phone' => '+998901112233',
            'full_address' => 'Toshkent sh., Chilonzor tumani, 9-mavze, 12-uy',
            'landmark' => 'Rayhon milliy taomlari yonida',
        ]);

        $res->assertRedirect();

        $address = Address::where('user_id', $customer->id)->where('title', 'Shahar Xonadoni')->first();
        $this->assertNotNull($address);
        $this->assertEquals('Azizbek', $address->recipient_name);

        // Delete address
        $delRes = $this->actingAs($customer)->delete("/profile/addresses/{$address->id}");
        $delRes->assertRedirect();

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_profile(): void
    {
        $res = $this->get('/profile');
        $res->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_orders_hub(): void
    {
        $res = $this->get('/orders');
        $res->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_view_orders_hub(): void
    {
        $customer = $this->createCustomer('hub_cust@example.uz');

        $res = $this->actingAs($customer)->get('/orders');
        $res->assertStatus(200)
            ->assertSee('Buyurtmalar va Yetkazib Berishlar')
            ->assertSee($customer->name);
    }

    public function test_customer_profile_renders_with_existing_orders_and_latest_payment(): void
    {
        $customer = $this->createCustomer('order_prof@example.uz');

        $seller = Seller::create([
            'name' => 'Demo Do\'kon',
            'legal_name' => 'Demo MCHJ',
            'slug' => 'demo-dokon',
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.9,
            'email' => 'seller_prof@example.uz',
            'phone' => '+998901112244',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-9999',
            'user_id' => $customer->id,
            'status' => 'processing',
            'currency' => 'UZS',
            'items_total' => 250000,
            'shipping_total' => 20000,
            'grand_total' => 270000,
            'shipping_address' => ['recipient_name' => 'Test', 'phone' => '+998901234567', 'full_address' => 'Toshkent'],
        ]);

        Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => 'ORD-TEST-9999-1',
            'status' => 'pending',
            'items_subtotal' => 250000,
            'shipping_cost' => 20000,
            'total_amount' => 270000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'transaction_id' => 'TXN-9999',
            'amount' => 270000,
            'currency' => 'UZS',
            'status' => 'pending',
        ]);

        $res = $this->actingAs($customer)->get('/profile');
        $res->assertStatus(200)
            ->assertSee('ORD-TEST-9999')
            ->assertDontSee('Server Error');
    }
}
