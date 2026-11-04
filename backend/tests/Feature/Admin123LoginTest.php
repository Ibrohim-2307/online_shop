<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Admin123LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = ['admin', 'seller_owner', 'customer', 'courier'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);
        }

        $accounts = [
            ['email' => 'admin@market.uz', 'role' => 'admin'],
            ['email' => 'seller@market.uz', 'role' => 'seller_owner'],
            ['email' => 'customer@market.uz', 'role' => 'customer'],
            ['email' => 'courier@market.uz', 'role' => 'courier'],
        ];

        foreach ($accounts as $acc) {
            $user = User::create([
                'name' => 'Demo ' . ucfirst($acc['role']),
                'email' => $acc['email'],
                'phone' => '+99890' . rand(1000000, 9999999),
                'password' => Hash::make('some_old_random_password'),
                'is_active' => true,
            ]);
            $role = Role::where('name', $acc['role'])->first();
            $user->roles()->attach($role->id);
        }
    }

    public function test_login_page_renders_with_admin123_quick_buttons(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('admin@market.uz');
        $response->assertSee('admin123');
        $response->assertSee('Tezkor Hisoblar');
    }

    public function test_all_demo_accounts_can_login_via_web_using_admin123(): void
    {
        $accounts = [
            'admin@market.uz' => '/admin',
            'seller@market.uz' => '/profile?tab=seller_products',
            'customer@market.uz' => '/profile',
            'courier@market.uz' => '/profile?tab=courier_shipments',
        ];

        foreach ($accounts as $email => $expectedRedirect) {
            $response = $this->post('/login', [
                'login' => $email,
                'password' => 'admin123',
            ]);

            $response->assertRedirect($expectedRedirect);
            $this->assertAuthenticated();

            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_demo_accounts_can_login_via_api_using_admin123(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'seller@market.uz',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.email', 'seller@market.uz');
        $this->assertNotEmpty($response->json('data.token'));
    }
}
