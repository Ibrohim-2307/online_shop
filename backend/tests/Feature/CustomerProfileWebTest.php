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
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\LiveTrackingSeeder::class);
        $this->seed(\Database\Seeders\SellerOrdersHubSeeder::class);

        $customer = User::where('email', 'customer@market.uz')->firstOrFail();

        $res = $this->actingAs($customer)->get('/profile');
        $res->assertStatus(200)
            ->assertDontSee('Server Error');

        $seller = User::where('email', 'seller@market.uz')->firstOrFail();
        $resSeller = $this->actingAs($seller)->get('/profile');
        $resSeller->assertStatus(200);

        $courier = User::where('email', 'courier@market.uz')->firstOrFail();
        $resCourier = $this->actingAs($courier)->get('/profile');
        $resCourier->assertStatus(200);

        $nullUser = User::create([
            'name' => 'No Phone User',
            'email' => 'nophone@example.uz',
            'phone' => null,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $resNull = $this->actingAs($nullUser)->get('/profile');
        $resNull->assertStatus(200);
    }

    public function test_admin_and_superadmin_can_view_profile(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $admin = User::create([
            'name' => 'Bosh Admin',
            'email' => 'admin_test_prof@example.uz',
            'phone' => '+998901112233',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole->id);

        $res = $this->actingAs($admin)->get('/profile');
        $res->assertStatus(200);

        $superRole = Role::firstOrCreate(['name' => 'superadmin'], ['label' => 'Superadmin']);
        $superadmin = User::create([
            'name' => 'Katta Superadmin',
            'email' => 'superadmin_test_prof@example.uz',
            'phone' => '+998901112244',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $superadmin->roles()->attach($superRole->id);

        $res2 = $this->actingAs($superadmin)->get('/profile');
        $res2->assertStatus(200);
    }

    public function test_seller_without_seller_id_can_view_profile(): void
    {
        $sellerRole = Role::firstOrCreate(['name' => 'seller_owner'], ['label' => 'Seller Owner']);
        $sellerUser = User::create([
            'name' => 'Yangi Sotuvchi',
            'email' => 'new_seller_prof@example.uz',
            'phone' => '+998901119988',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $sellerUser->roles()->attach($sellerRole->id);

        $res = $this->actingAs($sellerUser)->get('/profile');
        $res->assertStatus(200);
    }
}
