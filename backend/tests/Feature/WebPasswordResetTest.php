<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\Auth\OtpServiceInterface;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class WebPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->user = User::create([
            'name' => 'Dilnoza', 'email' => 'dilnoza@example.uz', 'phone' => '+998901234567',
            'password' => 'EskiParol123', 'is_active' => true,
        ]);
    }

    private function newPassword(string $code = '123456'): array
    {
        return ['code' => $code, 'password' => 'YangiParol123', 'password_confirmation' => 'YangiParol123'];
    }

    public function test_login_page_links_to_the_reset_page(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('/forgot-password', false);
        $this->get('/forgot-password')->assertStatus(200)->assertSee('Kod yuborish');
    }

    public function test_customer_resets_password_with_sms_code_and_can_log_in(): void
    {
        $this->post('/forgot-password', ['phone' => '+998 90 123 45 67'])
            ->assertRedirect('/reset-password')
            ->assertSessionHas('demo_code', '123456');

        $this->get('/reset-password')->assertStatus(200)->assertSee('+998901234567');

        $this->post('/reset-password', $this->newPassword())
            ->assertRedirect('/login')
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('YangiParol123', $this->user->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->user->id, 'action' => 'password.reset_completed']);

        $this->post('/login', ['login' => '+998901234567', 'password' => 'EskiParol123'])->assertSessionHasErrors('login');
        $this->post('/login', ['login' => '+998901234567', 'password' => 'YangiParol123'])->assertRedirect('/profile');
    }

    public function test_reset_logs_out_mobile_apps(): void
    {
        $this->user->createToken('phone');

        $this->post('/forgot-password', ['phone' => '+998901234567']);
        $this->post('/reset-password', $this->newPassword());

        $this->assertSame(0, $this->user->tokens()->count());
    }

    public function test_wrong_code_changes_nothing(): void
    {
        $this->post('/forgot-password', ['phone' => '+998901234567']);

        $this->post('/reset-password', $this->newPassword('654321'))->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('EskiParol123', $this->user->fresh()->password));
    }

    public function test_mistyped_new_password_does_not_burn_the_code(): void
    {
        $this->post('/forgot-password', ['phone' => '+998901234567']);

        $this->post('/reset-password', ['code' => '123456', 'password' => 'YangiParol123', 'password_confirmation' => 'Boshqa12345'])
            ->assertSessionHasErrors('password');

        $this->post('/reset-password', $this->newPassword())->assertRedirect('/login');
        $this->assertTrue(Hash::check('YangiParol123', $this->user->fresh()->password));
    }

    public function test_code_cannot_be_used_twice(): void
    {
        $this->post('/forgot-password', ['phone' => '+998901234567']);
        $this->post('/reset-password', $this->newPassword())->assertRedirect('/login');

        $this->withSession(['password_reset_phone' => '+998901234567'])
            ->post('/reset-password', ['code' => '123456', 'password' => 'Boshqa123456', 'password_confirmation' => 'Boshqa123456'])
            ->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('YangiParol123', $this->user->fresh()->password));
    }

    public function test_unknown_number_gets_the_same_answer_and_no_sms(): void
    {
        $this->post('/forgot-password', ['phone' => '+998909999999'])->assertRedirect('/reset-password');

        $this->assertSame(0, OtpCode::count());
        $this->post('/reset-password', $this->newPassword())->assertSessionHasErrors('code');
    }

    public function test_blocked_account_cannot_reset(): void
    {
        $this->user->update(['is_active' => false]);

        $this->post('/forgot-password', ['phone' => '+998901234567'])->assertRedirect('/reset-password');

        $this->assertSame(0, OtpCode::count());
    }

    public function test_reset_page_needs_a_started_request(): void
    {
        $this->get('/reset-password')->assertRedirect('/forgot-password');
        $this->post('/reset-password', $this->newPassword())->assertRedirect('/forgot-password');
    }

    public function test_one_number_cannot_be_flooded_with_sms(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->mock(OtpServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')->times(3)->andReturn(['success' => true, 'message' => 'ok']);
        });

        foreach (range(1, 5) as $attempt) {
            $this->post('/forgot-password', ['phone' => '+998901234567'])->assertRedirect('/reset-password');
        }
    }

    public function test_live_site_without_sms_provider_says_so_and_never_shows_a_code(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.sms.driver' => 'demo']);

        $this->get('/forgot-password')->assertStatus(200)->assertSee('administrator')->assertDontSee('Kod yuborish');
        $this->post('/forgot-password', ['phone' => '+998901234567'])->assertRedirect('/forgot-password');
        $this->get('/reset-password')->assertRedirect('/forgot-password');

        $this->assertSame(0, OtpCode::count());
    }
}
