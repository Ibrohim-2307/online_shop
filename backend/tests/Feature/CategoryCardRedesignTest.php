<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCardRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_home_page_displays_sleek_category_pods_with_emoji_and_dual_tone_vectors(): void
    {
        $seller = Seller::create([
            'name' => 'StroyBaza Grand',
            'slug' => 'stroybaza-grand',
            'legal_name' => 'StroyBaza Grand MCHJ',
            'tax_id' => '308123456',
            'status' => 'active',
            'rating' => 4.90,
        ]);

        $categories = [
            ['name' => 'Qurilish materiallari', 'slug' => 'qurilish-materiallari', 'icon' => '🧱'],
            ['name' => 'Sanoat uskunalari', 'slug' => 'sanoat-uskunalari', 'icon' => '🏭'],
            ['name' => 'Oziq-ovqat va ichimliklar', 'slug' => 'oziq-ovqat-va-ichimliklar', 'icon' => '🌾'],
            ['name' => 'Elektr va yoritish', 'slug' => 'elektr-jihozlari', 'icon' => '💡'],
            ['name' => 'Bo\'yoqlar va kimyo', 'slug' => 'boyoqlar-va-kimyo', 'icon' => '🎨'],
            ['name' => 'Santexnika va quvurlar', 'slug' => 'santexnika-va-quvurlar', 'icon' => '🚰'],
            ['name' => 'Asbob-uskunalar', 'slug' => 'asbob-uskunalar', 'icon' => '🛠️'],
        ];

        foreach ($categories as $index => $cat) {
            $created = Category::create([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'icon' => $cat['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            Product::create([
                'seller_id' => $seller->id,
                'category_id' => $created->id,
                'name' => 'Demo Item ' . $index,
                'slug' => 'demo-item-' . $index,
                'description' => 'Demo desc',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'status' => 'approved',
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertOk();

        // Verify each category name is visible
        foreach ($categories as $cat) {
            $response->assertSee($cat['name']);
            // Verify emoji is present in the rendered HTML
            $response->assertSee($cat['icon']);
        }

        // Verify sleek pod classes and dual-tone vector icons are rendered instead of tiny photo thumbnails
        $response->assertSee('viewBox="0 0 24 24"', false);
        $response->assertSee('stroke-linecap="round"', false);
        $response->assertSee('backdrop-blur-md', false);
        $response->assertSee('Ommabop Toifalar');
    }

    public function test_catalog_page_displays_category_quick_capsules_and_sidebar_with_emoji_styling(): void
    {
        $categories = [
            ['name' => 'Qurilish materiallari', 'slug' => 'qurilish-materiallari', 'icon' => '🧱'],
            ['name' => 'Sanoat uskunalari', 'slug' => 'sanoat-uskunalari', 'icon' => '🏭'],
        ];

        foreach ($categories as $index => $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'icon' => $cat['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('catalog'));

        $response->assertOk();
        $response->assertSee('Barcha toifalar');
        $response->assertSee('Qurilish materiallari');
        $response->assertSee('Sanoat uskunalari');
        $response->assertSee('🧱');
        $response->assertSee('🏭');

        // Verify filtering by category preserves emoji capsule active state
        $responseFiltered = $this->get(route('catalog', ['category' => 'qurilish-materiallari']));
        $responseFiltered->assertOk();
        $responseFiltered->assertSee('Qurilish materiallari');
    }
}
