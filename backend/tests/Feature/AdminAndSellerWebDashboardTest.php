<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Tests\TestCase;

class AdminAndSellerWebDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::where('email', 'customer@market.uz')->first() ?? User::factory()->create();
        $response = $this->actingAs($customer)->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('email', 'admin@market.uz')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin Test',
                'email' => 'admin_test@market.uz',
                'password' => bcrypt('Admin123!'),
                'is_active' => true,
            ]);
            $admin->assignRole('admin');
        }

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Admin Panel');
        $response->assertSee('Sotuvchilar');
        $response->assertSee('Foydalanuvchilar');
    }

    public function test_seller_can_access_seller_dashboard(): void
    {
        $sellerUser = User::where('email', 'seller@market.uz')->first();
        $response = $this->actingAs($sellerUser)->get('/seller');
        $response->assertStatus(200);
        $response->assertSee('Sotuvchi Kabineti', false);
        $response->assertSee('Do\'kon Balansi', false);
        $response->assertSee('Tovarlar', false);
    }

    public function test_admin_can_update_seller_status(): void
    {
        $admin = User::where('email', 'admin@market.uz')->first();
        $seller = Seller::first();

        $response = $this->actingAs($admin)->post("/admin/sellers/{$seller->id}/status", [
            'status' => 'suspended',
        ]);

        $response->assertRedirect();
        $this->assertEquals('suspended', $seller->fresh()->status);

        // Re-activate
        $this->actingAs($admin)->post("/admin/sellers/{$seller->id}/status", [
            'status' => 'active',
        ]);
        $this->assertEquals('active', $seller->fresh()->status);
    }
}
