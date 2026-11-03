<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecureAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        foreach (\App\Console\Commands\SecureAccountsCommand::DEMO_ACCOUNTS as $email => $password) {
            User::create(['name' => $email, 'email' => $email, 'password' => $password, 'is_active' => true]);
        }
        User::where('email', 'admin@market.uz')->first()->assignRole('admin');
    }

    protected function runInProduction(array $config = []): void
    {
        config(array_merge(['app.demo_mode' => false, 'app.admin_password' => null], $config));
        $this->app['env'] = 'production';
        $this->artisan('app:secure-accounts')->assertSuccessful();
        $this->app['env'] = 'testing';
    }

    public function test_admin_password_comes_from_environment_and_public_demo_passwords_are_locked(): void
    {
        $this->runInProduction(['app.admin_password' => 'Juda-Maxfiy-Parol-2026']);

        $admin = User::where('email', 'admin@market.uz')->first();
        $this->assertTrue(Hash::check('Juda-Maxfiy-Parol-2026', $admin->password));
        $this->assertFalse(Hash::check('Admin123!', $admin->password));

        foreach (['seller@market.uz' => 'Seller123!', 'customer@market.uz' => 'Customer123!', 'courier@market.uz' => 'Courier123!'] as $email => $public) {
            $this->assertFalse(Hash::check($public, User::where('email', $email)->first()->password), "{$email} still uses its public password");
        }

        // The published password no longer logs in; the new one does
        $this->post('/login', ['login' => 'admin@market.uz', 'password' => 'Admin123!'])->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->post('/login', ['login' => 'admin@market.uz', 'password' => 'Juda-Maxfiy-Parol-2026'])->assertRedirect('/admin');
    }

    public function test_without_admin_password_even_the_admin_demo_account_is_locked_in_production(): void
    {
        $this->runInProduction();

        $this->assertFalse(Hash::check('Admin123!', User::where('email', 'admin@market.uz')->first()->password));
    }

    public function test_new_admin_email_creates_superadmin_and_short_password_is_refused(): void
    {
        $this->runInProduction(['app.admin_email' => 'egasi@dokon.uz', 'app.admin_password' => 'qisqa']);
        $this->assertDatabaseMissing('users', ['email' => 'egasi@dokon.uz']);

        $this->runInProduction(['app.admin_email' => 'egasi@dokon.uz', 'app.admin_password' => 'Uzun-Va-Maxfiy-Parol']);
        $owner = User::where('email', 'egasi@dokon.uz')->firstOrFail();
        $this->assertTrue($owner->hasRole('superadmin'));
    }

    public function test_demo_mode_and_local_keep_demo_accounts(): void
    {
        $this->runInProduction(['app.demo_mode' => true]);
        $this->assertTrue(Hash::check('Seller123!', User::where('email', 'seller@market.uz')->first()->password));

        $this->artisan('app:secure-accounts')->assertSuccessful(); // testing env
        $this->assertTrue(Hash::check('Admin123!', User::where('email', 'admin@market.uz')->first()->password));
    }

    public function test_web_installer_is_closed_once_an_admin_exists(): void
    {
        $this->get('/install')->assertStatus(403);
    }
}
