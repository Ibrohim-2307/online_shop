<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallCommandAndControllerTest extends TestCase
{
    protected string $lockFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockFile = storage_path('installed.lock');
        if (File::exists($this->lockFile)) {
            File::delete($this->lockFile);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->lockFile)) {
            File::delete($this->lockFile);
        }
        parent::tearDown();
    }

    public function test_installer_screen_displays_requirements(): void
    {
        $response = $this->get('/install');
        $response->assertStatus(200);
        $response->assertSee('Hosting O\'rnatish Ustasi', false);
        $response->assertSee('PHP versiyasi', false);
    }

    public function test_installer_rejects_missing_or_invalid_token(): void
    {
        config(['app.install_token' => 'secret_install_key_123']);

        $response = $this->postJson('/install', [
            'install_token' => 'wrong_token',
            'admin_name' => 'Admin Test',
            'admin_email' => 'admin_test@market.uz',
            'admin_password' => 'SecurePass123!',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_installer_locks_after_successful_setup(): void
    {
        config(['app.install_token' => 'secret_install_key_123']);

        $response = $this->postJson('/install', [
            'install_token' => 'secret_install_key_123',
            'admin_name' => 'Super Administrator',
            'admin_email' => 'superadmin@market.uz',
            'admin_password' => 'SuperSecret123!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertFileExists($this->lockFile);

        // Subsequent GET /install must be 403 Forbidden
        $lockedGet = $this->get('/install');
        $lockedGet->assertStatus(403);
        $lockedGet->assertSee('O\'rnatish Qulflangan', false);

        // Subsequent POST /install must be 403 Forbidden
        $lockedPost = $this->postJson('/install', [
            'install_token' => 'secret_install_key_123',
            'admin_name' => 'Attacker',
            'admin_email' => 'attacker@market.uz',
            'admin_password' => 'Attacker123!',
        ]);
        $lockedPost->assertStatus(403);
    }

    public function test_cli_install_command_runs_and_locks(): void
    {
        $this->artisan('app:install', [
            '--admin-name' => 'CLI Admin',
            '--admin-email' => 'cliadmin@market.uz',
            '--admin-password' => 'CliAdminPass123!',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists($this->lockFile);

        // Second run without --force must fail
        $this->artisan('app:install', [
            '--admin-name' => 'CLI Admin 2',
            '--admin-email' => 'cliadmin2@market.uz',
            '--admin-password' => 'CliAdminPass123!',
        ])->assertFailed();
    }
}
