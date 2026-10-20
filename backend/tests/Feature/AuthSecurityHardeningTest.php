<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\OtpCode;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use App\Services\Auth\DemoOtpService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * Requirement 1: Password reset with time-limited single-use token; replay must be rejected.
     */
    public function test_password_reset_succeeds_and_token_replay_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'reset_test@example.uz',
            'password' => 'oldpassword123',
        ]);

        // 1. Request forgot password
        $forgotResponse = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'reset_test@example.uz',
        ]);

        $forgotResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $resetToken = $forgotResponse->json('data.reset_token');
        $this->assertNotNull($resetToken);

        // 2. Perform password reset
        $resetResponse = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'reset_test@example.uz',
            'token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $resetResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify password actually updated
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        // 3. Security Replay Test: Attempt to use the EXACT SAME token again -> must be rejected
        $replayResponse = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'reset_test@example.uz',
            'token' => $resetToken,
            'password' => 'hackedpassword123',
            'password_confirmation' => 'hackedpassword123',
        ]);

        $replayResponse->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'INVALID_TOKEN');

        // Verify password was NOT changed to hacked password
        $this->assertFalse(Hash::check('hackedpassword123', $user->fresh()->password));
    }

    /**
     * Requirement 1: Expired password reset token is rejected.
     */
    public function test_expired_password_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'expired_test@example.uz']);

        // Insert token with created_at 20 minutes ago (15 min TTL exceeded)
        $rawToken = 'expired-token-12345';
        DB::table('password_reset_tokens')->insert([
            'email' => 'expired_test@example.uz',
            'token' => Hash::make($rawToken),
            'created_at' => Carbon::now()->subMinutes(20),
        ]);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'expired_test@example.uz',
            'token' => $rawToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'EXPIRED_TOKEN');
    }

    /**
     * Requirement 2: Web session login regenerates session ID and logout invalidates session.
     */
    public function test_web_session_login_regenerates_session_and_logout_invalidates(): void
    {
        $user = User::factory()->create([
            'email' => 'webuser@example.uz',
            'password' => 'websecret123',
            'is_active' => true,
        ]);

        // Initial session
        $this->get('/login')->assertStatus(200);
        $initialSessionId = session()->getId();

        // Login with web form
        $loginResponse = $this->post('/login', [
            'login' => 'webuser@example.uz',
            'password' => 'websecret123',
        ]);

        $loginResponse->assertRedirect('/profile');
        $this->assertAuthenticatedAs($user);

        // Verify session regeneration occurred (session fixation defense)
        $newSessionId = session()->getId();
        $this->assertNotEquals($initialSessionId, $newSessionId);

        // Web logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Requirement 3: Admin MFA flow - Admin requires second factor before obtaining admin token.
     */
    public function test_admin_login_requires_mfa_and_blocks_unverified_access(): void
    {
        $admin = User::factory()->create([
            'email' => 'chief_admin@platform.uz',
            'phone' => '+998901110000',
            'password' => 'superadmin123',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // 1. Initial admin login returns mfa_required = true
        $loginResp = $this->postJson('/api/v1/auth/login', [
            'login' => 'chief_admin@platform.uz',
            'password' => 'superadmin123',
        ]);

        $loginResp->assertStatus(200)
            ->assertJsonPath('data.mfa_required', true)
            ->assertJsonStructure(['data' => ['mfa_token', 'mfa_required']]);

        $mfaToken = $loginResp->json('data.mfa_token');
        $this->assertNotNull($mfaToken);

        // Admin does NOT have an active session/token yet:
        // Trying to access admin endpoint with the mfa_token must fail!
        $unverifiedResp = $this->withHeader('Authorization', "Bearer {$mfaToken}")
            ->getJson('/api/v1/admin/users');
        $unverifiedResp->assertStatus(401);

        // 2. Verify MFA with valid code (demo code 123456)
        $mfaVerifyResp = $this->postJson('/api/v1/auth/mfa/verify', [
            'mfa_token' => $mfaToken,
            'code' => '123456',
        ]);

        $mfaVerifyResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $adminToken = $mfaVerifyResp->json('data.token');

        // 3. Now with verified admin token, access to admin endpoints succeeds!
        $adminUsersResp = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->getJson('/api/v1/admin/users');
        $adminUsersResp->assertStatus(200)->assertJsonPath('success', true);
    }

    /**
     * Requirement 4: OTP expiration, wrong attempts limit, and replay rejection.
     */
    public function test_otp_expiration_wrong_attempts_limit_and_replay_rejection(): void
    {
        // Bypass HTTP rate limiter to strictly test OTP service attempts & TTL logic
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $phone = '+998909998877';

        // 1. Expiration test
        $this->postJson('/api/v1/auth/otp/send', ['phone' => $phone]);
        $otp = OtpCode::where('phone', $phone)->first();
        $otp->update(['expires_at' => Carbon::now()->subMinutes(1)]); // Make expired

        $expiredResp = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => $phone,
            'code' => $otp->code,
        ]);
        $expiredResp->assertStatus(422)->assertJsonPath('error.code', 'INVALID_OTP');

        // 2. Wrong attempts threshold test (max 5 attempts)
        $this->postJson('/api/v1/auth/otp/send', ['phone' => $phone]);
        $freshOtp = OtpCode::where('phone', $phone)->whereNull('used_at')->first();

        // Submit wrong code 5 times
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/otp/verify', [
                'phone' => $phone,
                'code' => '000000',
            ]);
        }

        // 6th attempt with CORRECT code must now be rejected because max attempts exceeded!
        $lockedResp = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => $phone,
            'code' => $freshOtp->code,
        ]);
        $lockedResp->assertStatus(422);

        // 3. Replay rejection test
        $this->postJson('/api/v1/auth/otp/send', ['phone' => $phone]);
        $validOtp = OtpCode::where('phone', $phone)->whereNull('used_at')->first();

        // First verification succeeds
        $firstVerify = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => $phone,
            'code' => $validOtp->code,
        ]);
        $firstVerify->assertStatus(200)->assertJsonPath('success', true);

        // Second verification with the exact same code MUST fail (replay prevented)
        $secondVerify = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => $phone,
            'code' => $validOtp->code,
        ]);
        $secondVerify->assertStatus(422)->assertJsonPath('error.code', 'INVALID_OTP');
    }

    /**
     * Requirement 4: Login and OTP endpoints enforce rate limiting (HTTP 429 Too Many Requests).
     */
    public function test_login_and_otp_endpoints_enforce_rate_limiting(): void
    {
        $phone = '+998901239999';

        // Hit OTP send 5 times within 1 minute
        for ($i = 0; $i < 5; $i++) {
            $resp = $this->postJson('/api/v1/auth/otp/send', ['phone' => $phone]);
            $resp->assertStatus(200);
        }

        // 6th request must trigger rate limit 429 Too Many Requests
        $rateLimitedResp = $this->postJson('/api/v1/auth/otp/send', ['phone' => $phone]);
        $rateLimitedResp->assertStatus(429);
    }

    /**
     * Requirement 4: Demo OTP cannot run in production environment.
     */
    public function test_demo_otp_throws_exception_in_production(): void
    {
        $service = new DemoOtpService();

        // Switch environment to production
        app()['env'] = 'production';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/production/i');

        try {
            $service->send('+998901234567');
        } finally {
            // Restore testing environment
            app()['env'] = 'testing';
        }
    }

    /**
     * Requirement 5: Deactivated user's previously issued token is immediately blocked from APIs.
     */
    public function test_deactivated_user_previously_issued_token_is_immediately_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'active_first@example.uz',
            'is_active' => true,
        ]);
        $user->assignRole('customer');

        // User gets token while active
        $token = $user->createToken('test_auth_token')->plainTextToken;

        // Verify token works
        $firstResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');
        $firstResp->assertStatus(200);

        // Admin deactivates user
        $user->update(['is_active' => false]);

        // Attempting to access protected API with the existing token MUST now be rejected with 403 Forbidden!
        $blockedResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $blockedResp->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'ACCOUNT_DEACTIVATED');

        // Address endpoint also blocked
        $addressBlocked = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/addresses');
        $addressBlocked->assertStatus(403);
    }

    /**
     * Requirement 6: Registration request cannot escalate privileges to admin or moderator.
     */
    public function test_registration_privilege_escalation_is_strictly_neutralized(): void
    {
        $maliciousPayload = [
            'name' => 'Attacker',
            'email' => 'attacker@hack.uz',
            'phone' => '+998900000000',
            'password' => 'password12345',
            'account_type' => 'customer',
            // Injected elevation fields
            'role' => 'admin',
            'roles' => ['admin', 'moderator'],
            'is_admin' => true,
            'permissions' => ['*'],
        ];

        $response = $this->postJson('/api/v1/auth/register', $maliciousPayload);
        $response->assertStatus(201);

        $user = User::where('email', 'attacker@hack.uz')->first();
        $this->assertNotNull($user);

        // User must ONLY have customer role
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('moderator'));

        // User cannot access admin endpoints
        $token = $response->json('data.token');
        $adminAccess = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/users');
        $adminAccess->assertStatus(403);
    }

    /**
     * Requirement 7: Seller staff permissions - operator cannot add staff or promote to owner.
     */
    public function test_seller_operator_cannot_add_staff_or_promote_to_owner(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('seller_owner');

        $store = Seller::create(['name' => 'Store Staff Test', 'slug' => 'staff-test', 'status' => 'active']);
        SellerMember::create(['seller_id' => $store->id, 'user_id' => $owner->id, 'role' => 'owner']);

        $operator = User::factory()->create();
        $operator->assignRole('seller_operator');
        SellerMember::create(['seller_id' => $store->id, 'user_id' => $operator->id, 'role' => 'operator']);

        $candidate = User::factory()->create();

        // 1. Operator attempts to add a new staff member -> 403 Forbidden!
        $operatorAddResp = $this->actingAs($operator)->postJson("/api/v1/seller/{$store->id}/members", [
            'user_id' => $candidate->id,
            'role' => 'operator',
        ]);
        $operatorAddResp->assertStatus(403);

        // 2. Owner attempts to add a member with role 'owner' -> 422 Validation Error!
        $ownerAddOwnerResp = $this->actingAs($owner)->postJson("/api/v1/seller/{$store->id}/members", [
            'user_id' => $candidate->id,
            'role' => 'owner',
        ]);
        $ownerAddOwnerResp->assertStatus(422);
    }

    /**
     * Requirement 7: Suspended store management operations are restricted.
     */
    public function test_suspended_store_management_operations_are_blocked(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('seller_owner');

        $suspendedStore = Seller::create([
            'name' => 'Suspended Store',
            'slug' => 'suspended-store',
            'status' => 'suspended',
        ]);
        SellerMember::create([
            'seller_id' => $suspendedStore->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        // Attempting to update store profile while suspended -> 403 Forbidden!
        $updateResp = $this->actingAs($owner)->putJson("/api/v1/seller/{$suspendedStore->id}/profile", [
            'name' => 'New Name Attempt',
        ]);
        $updateResp->assertStatus(403);

        // Attempting to add member while suspended -> 403 Forbidden!
        $candidate = User::factory()->create();
        $addMemberResp = $this->actingAs($owner)->postJson("/api/v1/seller/{$suspendedStore->id}/members", [
            'user_id' => $candidate->id,
            'role' => 'operator',
        ]);
        $addMemberResp->assertStatus(403);
    }

    /**
     * Requirement 8: Security events are recorded in audit_logs and sensitive data is redacted.
     */
    public function test_audit_logs_record_security_events_without_passwords_or_tokens(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create(['is_active' => true]);

        // Admin deactivates user
        $this->actingAs($admin)->putJson("/api/v1/admin/users/{$targetUser->id}/status", [
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.status_updated',
            'entity_id' => $targetUser->id,
        ]);

        // Verify secret redaction
        $log = AuditLog::where('action', 'user.status_updated')->first();
        $this->assertNotNull($log);

        // Test AuditService::sanitize directly with secret values
        $sanitized = \App\Services\Audit\AuditService::sanitize([
            'username' => 'john',
            'password' => 'supersecret123',
            'token' => '1|abcdef1234567890',
            'nested' => [
                'api_secret' => 'topsecretkey',
                'regular_field' => 'visible',
            ],
        ]);

        $this->assertEquals('john', $sanitized['username']);
        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['token']);
        $this->assertEquals('[REDACTED]', $sanitized['nested']['api_secret']);
        $this->assertEquals('visible', $sanitized['nested']['regular_field']);
    }
}
