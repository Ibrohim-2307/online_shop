<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAndSellerWebDashboardTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_superadmin_can_reset_any_user_password(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $customer = User::where('email', 'customer@market.uz')->first();

        $response = $this->actingAs($superadmin)->post("/admin/users/{$customer->id}/password", [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewSecurePassword123!', $customer->fresh()->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin.user_password_reset',
            'user_id' => $superadmin->id,
            'entity_id' => $customer->id,
        ]);

        // Reset seller password
        $sellerUser = User::where('email', 'seller@market.uz')->first();
        if ($sellerUser) {
            $this->actingAs($superadmin)->post("/admin/users/{$sellerUser->id}/password", [
                'password' => 'SellerNewPass123!',
                'password_confirmation' => 'SellerNewPass123!',
            ])->assertSessionHas('success');
            $this->assertTrue(Hash::check('SellerNewPass123!', $sellerUser->fresh()->password));
        }

        // Reset courier password
        $courier = User::where('email', 'courier@market.uz')->first();
        if ($courier) {
            $this->actingAs($superadmin)->post("/admin/users/{$courier->id}/password", [
                'password' => 'CourierNewPass123!',
                'password_confirmation' => 'CourierNewPass123!',
            ])->assertSessionHas('success');
            $this->assertTrue(Hash::check('CourierNewPass123!', $courier->fresh()->password));
        }

        // Reset admin password
        $admin = User::where('email', 'admin@market.uz')->first();
        if ($admin) {
            $this->actingAs($superadmin)->post("/admin/users/{$admin->id}/password", [
                'password' => 'AdminNewPass123!',
                'password_confirmation' => 'AdminNewPass123!',
            ])->assertSessionHas('success');
            $this->assertTrue(Hash::check('AdminNewPass123!', $admin->fresh()->password));
        }
    }

    public function test_admin_cannot_reset_superadmin_password(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $admin = User::where('email', 'admin@market.uz')->first();

        $response = $this->actingAs($admin)->post("/admin/users/{$superadmin->id}/password", [
            'password' => 'HackedPassword123!',
            'password_confirmation' => 'HackedPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertFalse(Hash::check('HackedPassword123!', $superadmin->fresh()->password));
    }

    public function test_admin_cannot_reset_another_admin_or_moderator_password(): void
    {
        $admin1 = User::where('email', 'admin@market.uz')->first();

        $admin2 = User::factory()->create([
            'password' => bcrypt('OriginalAdmin2Pass123!'),
        ]);
        $admin2->assignRole('admin');

        $moderator = User::factory()->create([
            'password' => bcrypt('OriginalModPass123!'),
        ]);
        $moderator->assignRole('moderator');

        // Admin1 attempts to reset Admin2 password -> should fail
        $response1 = $this->actingAs($admin1)->post("/admin/users/{$admin2->id}/password", [
            'password' => 'HackedAdmin2Password123!',
            'password_confirmation' => 'HackedAdmin2Password123!',
        ]);
        $response1->assertRedirect();
        $response1->assertSessionHas('error');
        $this->assertFalse(Hash::check('HackedAdmin2Password123!', $admin2->fresh()->password));

        // Admin1 attempts to reset Moderator password -> should fail
        $response2 = $this->actingAs($admin1)->post("/admin/users/{$moderator->id}/password", [
            'password' => 'HackedModPassword123!',
            'password_confirmation' => 'HackedModPassword123!',
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('error');
        $this->assertFalse(Hash::check('HackedModPassword123!', $moderator->fresh()->password));
    }

    public function test_password_reset_revokes_tokens_and_database_sessions(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $customer = User::where('email', 'customer@market.uz')->first();

        // Create an API token for customer
        $customer->createToken('mobile-device');
        $this->assertGreaterThan(0, $customer->tokens()->count());

        // Create a fake database session for customer
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'fake_session_' . \Illuminate\Support\Str::random(10),
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'dummy_payload',
            'last_activity' => time(),
        ]);
        $this->assertEquals(1, \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $customer->id)->count());

        $oldRememberToken = $customer->remember_token;

        // Reset customer password
        $response = $this->actingAs($superadmin)->post("/admin/users/{$customer->id}/password", [
            'password' => 'NewSecuredPass123!',
            'password_confirmation' => 'NewSecuredPass123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify tokens deleted
        $this->assertEquals(0, $customer->fresh()->tokens()->count());

        // Verify sessions deleted
        $this->assertEquals(0, \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $customer->id)->count());

        // Verify remember token rotated
        $this->assertNotEquals($oldRememberToken, $customer->fresh()->remember_token);
    }

    public function test_password_reset_validation(): void
    {
        $admin = User::where('email', 'admin@market.uz')->first();
        $customer = User::where('email', 'customer@market.uz')->first();

        // Too short (< 8 chars)
        $response = $this->actingAs($admin)->post("/admin/users/{$customer->id}/password", [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $response->assertSessionHasErrors(['password']);

        // Password confirmation mismatch
        $response = $this->actingAs($admin)->post("/admin/users/{$customer->id}/password", [
            'password' => 'ValidPassword123',
            'password_confirmation' => 'DifferentPassword123',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_customer_cannot_reset_password_via_admin_endpoint(): void
    {
        $customer = User::where('email', 'customer@market.uz')->first();
        $courier = User::where('email', 'courier@market.uz')->first();

        $response = $this->actingAs($customer)->post("/admin/users/{$courier->id}/password", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_dashboard_renders_parol_button(): void
    {
        $admin = User::where('email', 'admin@market.uz')->first();
        $response = $this->actingAs($admin)->get('/admin?tab=users');

        $response->assertStatus(200);
        $response->assertSee('Parol');
        $response->assertSee('Foydalanuvchi Parolini Yangilash');
    }
}
