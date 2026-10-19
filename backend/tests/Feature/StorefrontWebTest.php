<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * Helper to create demo seller, category, and products with price tiers and inventory.
     */
    protected function createCatalogFixture(): array
    {
        $seller = Seller::create([
            'name' => 'StroyBaza Ulgurji Savdo',
            'slug' => 'stroybaza-ulgurji',
            'legal_name' => 'StroyBaza Grand MCHJ',
            'tax_id' => '308123456',
            'status' => 'active',
            'rating' => 4.90,
        ]);

        $category = Category::create([
            'name' => 'Qurilish materiallari',
            'slug' => 'qurilish-materiallari',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Qurilish Sementi M-500',
            'slug' => 'sement-m500',
            'description' => 'Yuqori sifatli portland sement M-500.',
            'trade_mode' => 'both',
            'unit' => 'piece',
            'unit_step' => 1.000,
            'min_order_quantity' => 1.000,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CEMENT-M500-BAG',
            'name' => '50 kg qop',
            'barcode' => '4780001234567',
            'is_active' => true,
        ]);

        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1.000,
            'max_quantity' => 299.000,
            'unit_price' => 300000,
            'currency' => 'UZS',
        ]);

        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 300.000,
            'max_quantity' => 999.000,
            'unit_price' => 270000,
            'currency' => 'UZS',
        ]);

        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1000.000,
            'max_quantity' => null,
            'unit_price' => 250000,
            'currency' => 'UZS',
        ]);

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => 'Asosiy Ombor (Sergeli)',
            'address' => 'Toshkent sh., Sergeli tumani',
            'is_active' => true,
        ]);

        InventoryBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => 50000.000,
            'reserved_quantity' => 0.000,
        ]);

        return [$seller, $category, $product, $variant, $warehouse];
    }

    public function test_home_page_returns_ok_and_displays_real_catalog(): void
    {
        $this->createCatalogFixture();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Chakana va Optom Savdo');
        $response->assertSee('Qurilish materiallari');
        $response->assertSee('Qurilish Sementi M-500');
        $response->assertSee('StroyBaza Ulgurji Savdo');
        $response->assertSee('300 000');
    }

    public function test_catalog_search_and_category_filtering(): void
    {
        [$seller, $category, $product] = $this->createCatalogFixture();

        // 1. Search with matching query
        $respSearch = $this->get('/catalog?q=sement');
        $respSearch->assertStatus(200);
        $respSearch->assertSee('Qurilish Sementi M-500');

        // 2. Search with category filter
        $respCat = $this->get('/catalog?category=qurilish-materiallari');
        $respCat->assertStatus(200);
        $respCat->assertSee('Qurilish Sementi M-500');

        // 3. Search query with 0 results displays empty state
        $respEmpty = $this->get('/catalog?q=mavjud_bolmagan_tovar_xyz');
        $respEmpty->assertStatus(200);
        $respEmpty->assertSee('Hech narsa topilmadi');
    }

    public function test_product_detail_page_displays_all_units_tiers_and_warehouse_stock(): void
    {
        $this->createCatalogFixture();

        $response = $this->get('/products/sement-m500');
        $response->assertStatus(200);
        $response->assertSee('Qurilish Sementi M-500');
        $response->assertSee('CEMENT-M500-BAG');
        $response->assertSee('Optom Narx Jadvali');
        $response->assertSee('Asosiy Ombor (Sergeli)');
        $response->assertSee('50 000 dona');

        // Nonexistent product returns 404
        $resp404 = $this->get('/products/mavjud-emas-404');
        $resp404->assertStatus(404);
    }

    public function test_seller_profile_page_displays_store_info_and_products(): void
    {
        $this->createCatalogFixture();

        $response = $this->get('/sellers/stroybaza-ulgurji');
        $response->assertStatus(200);
        $response->assertSee('StroyBaza Ulgurji Savdo');
        $response->assertSee('308123456'); // INN
        $response->assertSee('Qurilish Sementi M-500');

        // Nonexistent seller returns 404
        $resp404 = $this->get('/sellers/mavjud-emas-seller');
        $resp404->assertStatus(404);
    }

    public function test_wishlist_page_returns_ok_with_empty_state(): void
    {
        $response = $this->get('/wishlist');
        $response->assertStatus(200);
        $response->assertSee('Saqlangan Mahsulotlar');
        $response->assertSee("Sevimlilar ro'yxati bo'sh", false);
    }
}
