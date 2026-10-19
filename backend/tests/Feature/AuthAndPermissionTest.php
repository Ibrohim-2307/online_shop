<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions for tests
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * Test 1: Customer can register and receives Sanctum token and customer role.
     */
    public function test_customer_can_register_and_receives_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Alisher Navoiy',
            'email' => 'alisher@example.uz',
            'phone' => '+998901234567',
            'password' => 'secret12345',
            'account_type' => 'customer',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'phone', 'roles'],
                ],
                'request_id',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'alisher@example.uz']);
        $user = User::where('email', 'alisher@example.uz')->first();
        $this->assertTrue($user->hasRole('customer'));
    }

    /**
     * Test 2: Seller can register a store and is assigned seller_owner role and owner membership.
     */
    public function test_seller_can_register_store_and_becomes_seller_owner(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Bobur Mirzo',
            'email' => 'bobur@store.uz',
            'phone' => '+998909876543',
            'password' => 'secret12345',
            'account_type' => 'seller',
            'store_name' => 'Mirzo Savdo',
            'tax_id' => '123456789',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.seller.name', 'Mirzo Savdo')
            ->assertJsonPath('data.user.seller.role', 'owner');

        $user = User::where('email', 'bobur@store.uz')->first();
        $this->assertTrue($user->hasRole('seller_owner'));
        $this->assertDatabaseHas('sellers', ['name' => 'Mirzo Savdo', 'status' => 'pending']);
    }

    /**
     * Test 3: User can login with valid credentials and receive token.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login_test@example.uz',
            'password' => 'mysecurepassword',
            'is_active' => true,
        ]);
        $user->assignRole('customer');

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'login_test@example.uz',
            'password' => 'mysecurepassword',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);
    }

    /**
     * Test 4: Inactive (suspended) user cannot login.
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked@example.uz',
            'password' => 'mysecurepassword',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'blocked@example.uz',
            'password' => 'mysecurepassword',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test 5: OTP send and verify flow works with DemoOtpService.
     */
    public function test_otp_send_and_verify_flow(): void
    {
        $sendResponse = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '+998971112233',
            'type' => 'login',
        ]);

        $sendResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify with demo code 123456
        $verifyResponse = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '+998971112233',
            'code' => '123456',
            'type' => 'login',
            'name' => 'OTP Foydalanuvchisi',
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertDatabaseHas('users', ['phone' => '+998971112233']);
    }

    /**
     * Test 6: Authenticated user can view profile and logout (token is revoked).
     */
    public function test_authenticated_user_can_view_me_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'pass12345']);
        $user->assignRole('customer');
        $token = $user->createToken('test_token')->plainTextToken;

        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);

        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Token should now be deleted
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * Test 7: Address CRUD operates correctly for owner.
     */
    public function test_customer_can_create_and_read_own_address(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $response = $this->actingAs($user)->postJson('/api/v1/addresses', [
            'title' => 'Uy',
            'recipient_name' => 'Farhod',
            'phone' => '+998901234567',
            'full_address' => 'Toshkent sh., Chilonzor 9-mavze, 12-uy',
            'is_default' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Uy')
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'title' => 'Uy',
        ]);
    }

    /**
     * Test 8: User cannot view or update another user's address (403 Forbidden).
     */
    public function test_user_cannot_view_or_update_another_users_address(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('customer');

        $user2 = User::factory()->create();
        $user2->assignRole('customer');

        $addressOfUser1 = Address::create([
            'user_id' => $user1->id,
            'title' => 'User 1 Uyi',
            'recipient_name' => 'User 1',
            'phone' => '+998901111111',
            'full_address' => 'Manzil 1',
        ]);

        // User 2 attempts to view User 1's address -> 403 Forbidden
        $responseView = $this->actingAs($user2)->getJson("/api/v1/addresses/{$addressOfUser1->id}");
        $responseView->assertStatus(403);

        // User 2 attempts to update User 1's address -> 403 Forbidden
        $responseUpdate = $this->actingAs($user2)->putJson("/api/v1/addresses/{$addressOfUser1->id}", [
            'title' => 'Xakerlik qilingan manzil',
        ]);
        $responseUpdate->assertStatus(403);

        // User 2 attempts to delete User 1's address -> 403 Forbidden
        $responseDelete = $this->actingAs($user2)->deleteJson("/api/v1/addresses/{$addressOfUser1->id}");
        $responseDelete->assertStatus(403);
    }

    /**
     * Test 9: Multi-Tenant Isolation - Seller A cannot view Seller B's profile or members (403 Forbidden).
     */
    public function test_seller_a_cannot_view_seller_b_profile_or_members(): void
    {
        // Setup Seller Store A
        $sellerUserA = User::factory()->create();
        $sellerUserA->assignRole('seller_owner');
        $storeA = Seller::create([
            'name' => 'Do\'kon A',
            'slug' => 'dokon-a',
            'status' => 'active',
        ]);
        SellerMember::create([
            'seller_id' => $storeA->id,
            'user_id' => $sellerUserA->id,
            'role' => 'owner',
            'permissions' => ['*'],
        ]);

        // Setup Seller Store B
        $sellerUserB = User::factory()->create();
        $sellerUserB->assignRole('seller_owner');
        $storeB = Seller::create([
            'name' => 'Do\'kon B',
            'slug' => 'dokon-b',
            'status' => 'active',
        ]);
        SellerMember::create([
            'seller_id' => $storeB->id,
            'user_id' => $sellerUserB->id,
            'role' => 'owner',
            'permissions' => ['*'],
        ]);

        // Seller A tries to view Seller B's profile -> 403 Forbidden!
        $response = $this->actingAs($sellerUserA)->getJson("/api/v1/seller/{$storeB->id}/profile");
        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        // Seller A tries to view Seller B's members list -> 403 Forbidden!
        $responseMembers = $this->actingAs($sellerUserA)->getJson("/api/v1/seller/{$storeB->id}/members");
        $responseMembers->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * Test 10: Multi-Tenant Isolation - Seller A cannot modify Seller B's store profile (403 Forbidden).
     */
    public function test_seller_a_cannot_update_seller_b_store_profile(): void
    {
        $sellerUserA = User::factory()->create();
        $sellerUserA->assignRole('seller_owner');
        $storeA = Seller::create(['name' => 'Store A', 'slug' => 'store-a', 'status' => 'active']);
        SellerMember::create(['seller_id' => $storeA->id, 'user_id' => $sellerUserA->id, 'role' => 'owner']);

        $sellerUserB = User::factory()->create();
        $sellerUserB->assignRole('seller_owner');
        $storeB = Seller::create(['name' => 'Store B', 'slug' => 'store-b', 'status' => 'active']);
        SellerMember::create(['seller_id' => $storeB->id, 'user_id' => $sellerUserB->id, 'role' => 'owner']);

        // Seller A attempts to overwrite Store B's name and tax_id
        $response = $this->actingAs($sellerUserA)->putJson("/api/v1/seller/{$storeB->id}/profile", [
            'name' => 'Hacked Store Name',
            'tax_id' => '000000000',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('sellers', ['name' => 'Hacked Store Name']);
        $this->assertEquals('Store B', $storeB->fresh()->name);
    }

    /**
     * Test 11: Multi-Tenant Isolation - Seller A cannot remove staff from Seller B's store (403 Forbidden).
     */
    public function test_seller_a_cannot_remove_seller_b_staff_member(): void
    {
        $sellerUserA = User::factory()->create();
        $sellerUserA->assignRole('seller_owner');
        $storeA = Seller::create(['name' => 'Store A', 'slug' => 'store-a', 'status' => 'active']);
        SellerMember::create(['seller_id' => $storeA->id, 'user_id' => $sellerUserA->id, 'role' => 'owner']);

        $sellerUserB = User::factory()->create();
        $sellerUserB->assignRole('seller_owner');
        $storeB = Seller::create(['name' => 'Store B', 'slug' => 'store-b', 'status' => 'active']);
        SellerMember::create(['seller_id' => $storeB->id, 'user_id' => $sellerUserB->id, 'role' => 'owner']);

        $staffUserB = User::factory()->create();
        $staffUserB->assignRole('seller_operator');
        $memberB = SellerMember::create([
            'seller_id' => $storeB->id,
            'user_id' => $staffUserB->id,
            'role' => 'operator',
        ]);

        // Seller A attempts to delete Seller B's staff member
        $response = $this->actingAs($sellerUserA)->deleteJson("/api/v1/seller/{$storeB->id}/members/{$memberB->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('seller_members', ['id' => $memberB->id]);
    }

    /**
     * Test 12: Seller can manage own store profile and own members successfully.
     */
    public function test_seller_can_manage_own_store_and_add_staff(): void
    {
        $sellerUser = User::factory()->create();
        $sellerUser->assignRole('seller_owner');
        $store = Seller::create(['name' => 'Ezgulik Do\'koni', 'slug' => 'ezgulik', 'status' => 'active']);
        SellerMember::create(['seller_id' => $store->id, 'user_id' => $sellerUser->id, 'role' => 'owner']);

        $operatorCandidate = User::factory()->create();

        // 1. View own profile
        $viewResp = $this->actingAs($sellerUser)->getJson('/api/v1/seller/profile');
        $viewResp->assertStatus(200)->assertJsonPath('data.name', 'Ezgulik Do\'koni');

        // 2. Update own profile
        $updateResp = $this->actingAs($sellerUser)->putJson('/api/v1/seller/profile', [
            'name' => 'Ezgulik Supermarket',
            'phone' => '+998901112233',
        ]);
        $updateResp->assertStatus(200);
        $this->assertEquals('Ezgulik Supermarket', $store->fresh()->name);

        // 3. Add operator staff member
        $addMemberResp = $this->actingAs($sellerUser)->postJson('/api/v1/seller/members', [
            'user_id' => $operatorCandidate->id,
            'role' => 'operator',
        ]);
        $addMemberResp->assertStatus(201);
        $this->assertDatabaseHas('seller_members', [
            'seller_id' => $store->id,
            'user_id' => $operatorCandidate->id,
            'role' => 'operator',
        ]);
    }

    /**
     * Test 13: Customer cannot access admin endpoints (403 Forbidden).
     */
    public function test_customer_cannot_access_admin_endpoints(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        // Attempt to view users list
        $responseUsers = $this->actingAs($customer)->getJson('/api/v1/admin/users');
        $responseUsers->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        // Attempt to view all sellers
        $responseSellers = $this->actingAs($customer)->getJson('/api/v1/admin/sellers');
        $responseSellers->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        // Attempt to change seller status
        $responseStatus = $this->actingAs($customer)->putJson('/api/v1/admin/sellers/1/status', [
            'status' => 'active',
        ]);
        $responseStatus->assertStatus(403);
    }

    /**
     * Test 14: Customer cannot access seller endpoints (403 Forbidden).
     */
    public function test_customer_cannot_access_seller_endpoints(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->getJson('/api/v1/seller/profile');
        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * Test 15: Platform Admin can access admin endpoints and moderate sellers.
     */
    public function test_admin_can_access_admin_endpoints_and_moderate_sellers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::create([
            'name' => 'Tekshirilayotgan Do\'kon',
            'slug' => 'tekshiruv',
            'status' => 'pending',
        ]);

        // Admin lists users
        $respUsers = $this->actingAs($admin)->getJson('/api/v1/admin/users');
        $respUsers->assertStatus(200)->assertJsonPath('success', true);

        // Admin lists sellers
        $respSellers = $this->actingAs($admin)->getJson('/api/v1/admin/sellers');
        $respSellers->assertStatus(200)->assertJsonPath('success', true);

        // Admin approves seller (pending -> active)
        $respApprove = $this->actingAs($admin)->putJson("/api/v1/admin/sellers/{$seller->id}/status", [
            'status' => 'active',
            'commission_rate' => 5.50,
        ]);

        $respApprove->assertStatus(200)
            ->assertJsonPath('data.status', 'active');

        $this->assertEquals('active', $seller->fresh()->status);
        $this->assertEquals(5.50, $seller->fresh()->commission_rate);
    }
}
