<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApkDownloadWebTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(): User
    {
        $role = Role::firstOrCreate(['name' => 'customer'], ['label' => 'Xaridor']);
        $user = User::create([
            'name' => 'Dilshod Xaridor',
            'email' => 'dilshod@example.uz',
            'phone' => '+998901234567',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $user->roles()->attach($role->id);

        return $user;
    }

    public function test_apk_index_redirects_to_profile_with_apk_tab(): void
    {
        $res = $this->get('/download/apk');
        $res->assertRedirect(route('profile', ['tab' => 'apk_download']));
    }

    public function test_apk_info_json_endpoint_returns_metadata(): void
    {
        $res = $this->getJson('/download/apk/info');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('latest_version', 'v2.4.0')
            ->assertJsonStructure([
                'success',
                'latest_version',
                'apks' => [
                    'superapp' => ['key', 'filename', 'name', 'download_url', 'file_size_formatted'],
                    'xaridor' => ['key', 'filename', 'name', 'download_url', 'file_size_formatted'],
                    'kuryer' => ['key', 'filename', 'name', 'download_url', 'file_size_formatted'],
                ],
            ]);
    }

    public function test_api_v1_mobile_apks_endpoint_works(): void
    {
        $res = $this->getJson('/api/v1/mobile/apks');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('apks.superapp.filename', 'Marketplace_SuperApp.apk');
    }

    public function test_download_superapp_apk_returns_proper_android_headers(): void
    {
        $res = $this->get('/download/apk/superapp');
        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/vnd.android.package-archive');
        $this->assertStringContainsString('Marketplace_SuperApp.apk', (string)$res->headers->get('content-disposition'));
        $res->assertHeader('x-apk-type', 'superapp');
    }

    public function test_download_xaridor_apk_returns_proper_android_headers(): void
    {
        $res = $this->get('/download/apk/xaridor');
        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/vnd.android.package-archive');
        $this->assertStringContainsString('Marketplace_Xaridor.apk', (string)$res->headers->get('content-disposition'));
        $res->assertHeader('x-apk-type', 'xaridor');
    }

    public function test_download_kuryer_apk_returns_proper_android_headers(): void
    {
        $res = $this->get('/download/apk/kuryer');
        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/vnd.android.package-archive');
        $this->assertStringContainsString('Marketplace_Kuryer.apk', (string)$res->headers->get('content-disposition'));
        $res->assertHeader('x-apk-type', 'kuryer');
    }

    public function test_download_with_alias_works(): void
    {
        $res = $this->get('/download/apk/courier');
        $res->assertStatus(200);
        $this->assertStringContainsString('Marketplace_Kuryer.apk', (string)$res->headers->get('content-disposition'));

        $resMarketIlova = $this->get('/download/apk/market-ilova');
        $resMarketIlova->assertStatus(200);
        $this->assertStringContainsString('Marketplace_SuperApp.apk', (string)$resMarketIlova->headers->get('content-disposition'));

        $resDirect = $this->get('/downloads/market-ilova.apk');
        $resDirect->assertRedirect('/download/apk/superapp');
    }

    public function test_download_invalid_type_returns_404(): void
    {
        $res = $this->get('/download/apk/non_existent_app');
        $res->assertStatus(404);
    }

    public function test_profile_displays_unified_superapp_showcase_and_roles(): void
    {
        $customer = $this->createCustomer();

        $res = $this->actingAs($customer)->get('/profile?tab=apk_download');
        $res->assertStatus(200)
            ->assertSee('Marketplace SuperApp')
            ->assertSee('Barcha rollar 1 ta ilovada')
            ->assertSee('53.5 MB')
            ->assertSee('Xaridor')
            ->assertSee('Sotuvchi')
            ->assertSee('Kuryer')
            ->assertSee('Admin')
            ->assertSee('/download/apk/superapp')
            ->assertSee('3 qadamli', false);
    }
}
