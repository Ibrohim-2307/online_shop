<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Promocode;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin_adv@market.uz',
            'phone' => '+998901112233',
            'password' => bcrypt('AdminSecret123!'),
            'is_active' => true,
        ]);
        $user->roles()->attach($role->id);
        return $user;
    }

    public function test_admin_can_view_analytics_and_export_orders_csv(): void
    {
        $admin = $this->createAdmin();

        $res = $this->actingAs($admin)->get('/admin?tab=analytics');
        $res->assertStatus(200)
            ->assertSee('Savdo Analitikasi')
            ->assertSee('Savdo Dinamikasi');

        // Export Orders CSV
        $exportRes = $this->actingAs($admin)->get('/admin/export/orders');
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportRes->headers->get('content-type'));

        // Export Sellers CSV
        $sellerExportRes = $this->actingAs($admin)->get('/admin/export/sellers');
        $sellerExportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $sellerExportRes->headers->get('content-type'));
    }

    public function test_admin_can_create_and_manage_categories(): void
    {
        $admin = $this->createAdmin();

        $res = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Qurilish Asboblari Yangi',
            'slug' => 'qurilish-asboblari-yangi',
            'icon' => '🛠️',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Qurilish Asboblari Yangi',
            'slug' => 'qurilish-asboblari-yangi',
            'icon' => '🛠️',
        ]);

        $cat = Category::where('slug', 'qurilish-asboblari-yangi')->first();
        $this->assertNotNull($cat);

        // Toggle category status
        $toggleRes = $this->actingAs($admin)->post("/admin/categories/{$cat->id}/status");
        $toggleRes->assertRedirect();
        $this->assertFalse((bool)$cat->fresh()->is_active);
    }

    public function test_admin_can_create_promocode_and_customer_can_apply_it(): void
    {
        $admin = $this->createAdmin();

        // 1. Admin creates promocode
        $createRes = $this->actingAs($admin)->post('/admin/promocodes', [
            'code' => 'SUPER20',
            'title' => '20 ming so\'m chegirma',
            'type' => 'fixed',
            'value' => 20000,
            'min_order_amount' => 50000,
            'is_active' => 1,
        ]);

        $createRes->assertRedirect();
        $this->assertDatabaseHas('promocodes', [
            'code' => 'SUPER20',
            'value' => 20000,
            'type' => 'fixed',
        ]);

        // 2. Customer checks promocode via AJAX check endpoint
        $checkRes = $this->postJson('/checkout/promocode/check', [
            'code' => 'SUPER20',
            'subtotal' => 150000,
        ]);

        $checkRes->assertStatus(200);
        $checkRes->assertJson([
            'success' => true,
            'data' => [
                'code' => 'SUPER20',
                'discount' => 20000,
            ],
        ]);

        // 3. Reject if under min order amount
        $invalidRes = $this->postJson('/checkout/promocode/check', [
            'code' => 'SUPER20',
            'subtotal' => 30000,
        ]);
        $invalidRes->assertStatus(422);
    }

    public function test_admin_can_create_and_manage_marketing_banners(): void
    {
        $admin = $this->createAdmin();

        $res = $this->actingAs($admin)->post('/admin/banners', [
            'title' => 'Katta Yozgi Chegirmalar',
            'image_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e',
            'link_url' => '/catalog?category=qurilish-materiallari',
            'position' => 'home_hero',
            'is_active' => 1,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('banners', [
            'title' => 'Katta Yozgi Chegirmalar',
        ]);
    }
}
