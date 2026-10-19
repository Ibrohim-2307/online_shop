<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryService;
use App\Services\Pricing\PricingService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndPricingTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $pricingService;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->pricingService = new PricingService();
        $this->inventoryService = new InventoryService();
    }

    /**
     * Helper to create a seller with an owner user.
     */
    protected function createSeller(string $name, string $slug): array
    {
        $user = User::factory()->create();
        $user->assignRole('seller_owner');

        $seller = Seller::create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
        ]);

        SellerMember::create([
            'seller_id' => $seller->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'permissions' => ['*'],
        ]);

        return [$seller, $user];
    }

    /**
     * Requirement 1: 299, 300, 999 and 1000 threshold price calculations with all-units tiered pricing.
     * Example A from TECHNICAL_SPEC.md:
     * 1-299 units @ 300,000 UZS / unit
     * 300-999 units @ 270,000 UZS / unit
     * 1000+ units @ 250,000 UZS / unit
     */
    public function test_pricing_tiers_at_299_300_999_and_1000_thresholds(): void
    {
        [$seller, $user] = $this->createSeller('Ulgurji Do\'kon', 'ulgurji-dokon');

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Qurilish Sementi M-500',
            'slug' => 'sement-m500',
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
        ]);

        // Tier 1: 1 - 299 @ 300,000 UZS
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1.000,
            'max_quantity' => 299.000,
            'unit_price' => 300000,
            'currency' => 'UZS',
        ]);

        // Tier 2: 300 - 999 @ 270,000 UZS
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 300.000,
            'max_quantity' => 999.000,
            'unit_price' => 270000,
            'currency' => 'UZS',
        ]);

        // Tier 3: 1000+ @ 250,000 UZS
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1000.000,
            'max_quantity' => null,
            'unit_price' => 250000,
            'currency' => 'UZS',
        ]);

        // 1. Boundary 299 units: 299 * 300,000 = 89,700,000 UZS
        $quote299 = $this->pricingService->calculateQuote($variant, 299);
        $this->assertEquals('300000', $quote299['unit_price']);
        $this->assertEquals('89700000', $quote299['total_price']);

        // 2. Boundary 300 units: 300 * 270,000 = 81,000,000 UZS
        // NOTE: 300 units total (81m) is cheaper than 299 units total (89.7m) under all-units pricing!
        $quote300 = $this->pricingService->calculateQuote($variant, 300);
        $this->assertEquals('270000', $quote300['unit_price']);
        $this->assertEquals('81000000', $quote300['total_price']);
        $this->assertTrue(bccomp($quote300['total_price'], $quote299['total_price']) < 0);

        // 3. Boundary 999 units: 999 * 270,000 = 269,730,000 UZS
        $quote999 = $this->pricingService->calculateQuote($variant, 999);
        $this->assertEquals('270000', $quote999['unit_price']);
        $this->assertEquals('269730000', $quote999['total_price']);

        // 4. Boundary 1000 units: 1000 * 250,000 = 250,000,000 UZS
        $quote1000 = $this->pricingService->calculateQuote($variant, 1000);
        $this->assertEquals('250000', $quote1000['unit_price']);
        $this->assertEquals('250000000', $quote1000['total_price']);
        $this->assertTrue(bccomp($quote1000['total_price'], $quote999['total_price']) < 0);

        // Also test API endpoint for quote calculation
        $response = $this->postJson("/api/v1/products/{$variant->id}/quote", ['quantity' => 300]);
        $response->assertStatus(200)
            ->assertJsonPath('data.total_price', '81000000')
            ->assertJsonPath('data.unit_price', '270000');
    }

    /**
     * Requirement 2: No floating-point math for money calculations.
     * Prevents binary precision loss on high-value equipment or large order quantities.
     */
    public function test_money_calculations_strictly_avoid_float_precision_loss(): void
    {
        [$seller, $user] = $this->createSeller('Uskunalar Zavodi', 'uskunalar');

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Sanoat Liniyasi Generator',
            'slug' => 'sanoat-liniyasi',
            'trade_mode' => 'both',
            'unit' => 'piece',
            'unit_step' => 1.000,
            'min_order_quantity' => 1.000,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GEN-IND-300M',
            'name' => '300 kW Generator',
        ]);

        // Retail: 300,000,000 UZS per unit; Wholesale: 270,000,000 UZS per unit
        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 1.000,
            'max_quantity' => 9.000,
            'unit_price' => 300000000,
            'currency' => 'UZS',
        ]);

        PriceTier::create([
            'product_variant_id' => $variant->id,
            'min_quantity' => 10.000,
            'max_quantity' => null,
            'unit_price' => 270000000,
            'currency' => 'UZS',
        ]);

        // High volume calculation: 100,000 units @ 270,000,000 UZS = 27,000,000,000,000 UZS (27 trillion)
        // In native PHP float, 100000 * 270000000 becomes 2.7E+13 with precision degradation.
        $quote = $this->pricingService->calculateQuote($variant, 100000);

        $expectedTotal = '27000000000000';
        $this->assertIsString($quote['total_price']);
        $this->assertEquals($expectedTotal, $quote['total_price']);
    }

    /**
     * Requirement 3: Units (piece, box, kg, ton) and unit_step constraints validation.
     */
    public function test_units_and_unit_step_constraints_validation(): void
    {
        [$seller, $user] = $this->createSeller('Oziq-Ovqat Hub', 'oziq-ovqat');

        // 1. Piece unit (dona) - cannot be fractional
        $pieceProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Choynak',
            'slug' => 'choynak',
            'unit' => 'piece',
            'unit_step' => 1.000,
            'min_order_quantity' => 1.000,
            'status' => 'approved',
        ]);

        $validPiece = $this->pricingService->validateQuantity($pieceProduct, 5);
        $this->assertTrue($validPiece['valid']);

        $invalidPiece = $this->pricingService->validateQuantity($pieceProduct, 2.5);
        $this->assertFalse($invalidPiece['valid']);
        $this->assertStringContainsString('butun son', $invalidPiece['error']);

        // 2. Box unit (quti) - items per box conversion
        $boxProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Mineral Suv',
            'slug' => 'mineral-suv',
            'unit' => 'box',
            'items_per_box' => 24,
            'unit_step' => 1.000,
            'min_order_quantity' => 2.000,
            'status' => 'approved',
        ]);

        // Less than min order quantity (2 boxes)
        $belowMinBox = $this->pricingService->validateQuantity($boxProduct, 1);
        $this->assertFalse($belowMinBox['valid']);

        // 5 boxes = 120 effective bottles
        $validBox = $this->pricingService->validateQuantity($boxProduct, 5);
        $this->assertTrue($validBox['valid']);
        $this->assertEquals('120', $validBox['effective_units']);

        // 3. Kg and Ton units (1 ton = 1,000 kg)
        $tonProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Armatura',
            'slug' => 'armatura',
            'unit' => 'ton',
            'unit_step' => 0.500,
            'min_order_quantity' => 1.000,
            'status' => 'approved',
        ]);

        // 2.5 tons = 2,500 kg
        $validTon = $this->pricingService->validateQuantity($tonProduct, 2.5);
        $this->assertTrue($validTon['valid']);
        $this->assertEquals('2500.000', $validTon['effective_units']);

        // 4. Unit step validation (e.g. step = 5, min = 10 -> 10, 15, 20 are valid; 12 is invalid)
        $stepProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Blok G\'isht',
            'slug' => 'blok-gisht',
            'unit' => 'piece',
            'unit_step' => 5.000,
            'min_order_quantity' => 10.000,
            'status' => 'approved',
        ]);

        $this->assertTrue($this->pricingService->validateQuantity($stepProduct, 10)['valid']);
        $this->assertTrue($this->pricingService->validateQuantity($stepProduct, 15)['valid']);
        $this->assertTrue($this->pricingService->validateQuantity($stepProduct, 25)['valid']);

        $invalidStep = $this->pricingService->validateQuantity($stepProduct, 12);
        $this->assertFalse($invalidStep['valid']);
        $this->assertStringContainsString('qadamiga', $invalidStep['error']);
    }

    /**
     * Requirement 4: Multi-tenant catalog isolation - Seller A cannot view, update, or delete Seller B's product/tiers.
     */
    public function test_seller_cannot_view_update_or_delete_another_sellers_product(): void
    {
        [$sellerA, $userA] = $this->createSeller('Do\'kon A', 'dokon-a');
        [$sellerB, $userB] = $this->createSeller('Do\'kon B', 'dokon-b');

        $productB = Product::create([
            'seller_id' => $sellerB->id,
            'name' => 'Do\'kon B ning maxfiy mahsuloti',
            'slug' => 'maxfiy-mahsulot-b',
            'trade_mode' => 'both',
            'unit' => 'piece',
            'status' => 'pending', // Draft/pending product is not public
            'is_active' => true,
        ]);

        $variantB = ProductVariant::create([
            'product_id' => $productB->id,
            'sku' => 'SKU-B-SECRET',
            'name' => 'Variant B',
        ]);

        // 1. Seller A attempts to view Seller B's pending product -> 403 Forbidden!
        $viewResp = $this->actingAs($userA)->getJson("/api/v1/seller/products/{$productB->id}");
        $viewResp->assertStatus(403);

        // 2. Seller A attempts to update Seller B's product -> 403 Forbidden!
        $updateResp = $this->actingAs($userA)->putJson("/api/v1/seller/products/{$productB->id}", [
            'name' => 'Hacked Name by Seller A',
        ]);
        $updateResp->assertStatus(403);
        $this->assertEquals('Do\'kon B ning maxfiy mahsuloti', $productB->fresh()->name);

        // 3. Seller A attempts to set price tiers on Seller B's variant -> 403 Forbidden!
        $tierResp = $this->actingAs($userA)->postJson("/api/v1/seller/variants/{$variantB->id}/price-tiers", [
            'tiers' => [
                ['min_quantity' => 1, 'max_quantity' => 10, 'unit_price' => 1000],
            ],
        ]);
        $tierResp->assertStatus(403);

        // 4. Seller A attempts to delete Seller B's product -> 403 Forbidden!
        $deleteResp = $this->actingAs($userA)->deleteJson("/api/v1/seller/products/{$productB->id}");
        $deleteResp->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $productB->id]);
    }

    /**
     * Requirement 4: Multi-tenant warehouse isolation - Seller A cannot view, update, or adjust Seller B's warehouse.
     */
    public function test_seller_cannot_view_update_or_adjust_another_sellers_warehouse(): void
    {
        [$sellerA, $userA] = $this->createSeller('Do\'kon A', 'dokon-a-wh');
        [$sellerB, $userB] = $this->createSeller('Do\'kon B', 'dokon-b-wh');

        $warehouseB = Warehouse::create([
            'seller_id' => $sellerB->id,
            'name' => 'Ombor B',
            'address' => 'Sergeli sanoat zonasi',
            'is_active' => true,
        ]);

        $productB = Product::create([
            'seller_id' => $sellerB->id,
            'name' => 'Mahsulot B',
            'slug' => 'mahsulot-b',
            'unit' => 'piece',
            'status' => 'approved',
        ]);
        $variantB = ProductVariant::create(['product_id' => $productB->id, 'sku' => 'SKU-B-WH', 'name' => 'B']);

        // 1. Seller A attempts to view Seller B's warehouse -> 403 Forbidden!
        $viewResp = $this->actingAs($userA)->getJson("/api/v1/seller/warehouses/{$warehouseB->id}");
        $viewResp->assertStatus(403);

        // 2. Seller A attempts to update Seller B's warehouse -> 403 Forbidden!
        $updateResp = $this->actingAs($userA)->putJson("/api/v1/seller/warehouses/{$warehouseB->id}", [
            'name' => 'Hacked Warehouse Name',
        ]);
        $updateResp->assertStatus(403);
        $this->assertEquals('Ombor B', $warehouseB->fresh()->name);

        // 3. Seller A attempts to adjust stock in Seller B's warehouse -> 403 Forbidden!
        $adjustResp = $this->actingAs($userA)->postJson('/api/v1/seller/inventory/adjust', [
            'warehouse_id' => $warehouseB->id,
            'product_variant_id' => $variantB->id,
            'quantity_delta' => 100,
            'type' => 'inflow',
            'reason' => 'Unauthorized injection',
        ]);
        $adjustResp->assertStatus(403);
    }

    /**
     * Requirement 5: Inventory balance adjustments create immutable audit history movements.
     */
    public function test_inventory_movements_history_recorded_on_every_stock_change(): void
    {
        [$seller, $user] = $this->createSeller('Markaziy Ombor', 'markaziy-ombor');

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => 'Toshkent Asosiy Ombor',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Smartfon Pro',
            'slug' => 'smartfon-pro',
            'unit' => 'piece',
            'status' => 'approved',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PHONE-PRO-BLACK',
            'name' => 'Qora rang',
        ]);

        // 1. Stock Inflow: +100 units
        $balance = $this->inventoryService->adjustStock(
            warehouse: $warehouse,
            variant: $variant,
            quantityDelta: 100,
            type: 'inflow',
            reason: 'Yetkazib beruvchidan qabul qilindi',
            actor: $user
        );

        $this->assertEquals('100.000', $balance->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'type' => 'inflow',
            'balance_before' => '0.000',
            'balance_after' => '100.000',
            'reason' => 'Yetkazib beruvchidan qabul qilindi',
        ]);

        // 2. Stock Outflow: -30 units
        $balanceAfterOutflow = $this->inventoryService->adjustStock(
            warehouse: $warehouse,
            variant: $variant,
            quantityDelta: -30,
            type: 'outflow',
            reason: 'Boshqa filialga o\'tkazildi',
            actor: $user
        );

        $this->assertEquals('70.000', $balanceAfterOutflow->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'type' => 'outflow',
            'balance_before' => '100.000',
            'balance_after' => '70.000',
        ]);

        // 3. Overselling protection: attempting -80 units when current is 70 must throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/yetarli zaxira mavjud emas/i');

        $this->inventoryService->adjustStock(
            warehouse: $warehouse,
            variant: $variant,
            quantityDelta: -80,
            type: 'outflow',
            reason: 'Oversell attempt',
            actor: $user
        );
    }

    /**
     * Requirement 5: Stock reservation and release tracking.
     */
    public function test_inventory_reservation_and_release_tracking(): void
    {
        [$seller, $user] = $this->createSeller('Ombor Zaxira', 'ombor-zaxira');

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => 'Filial Ombor',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Noutbuk Air',
            'slug' => 'noutbuk-air',
            'unit' => 'piece',
            'status' => 'approved',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'LAPTOP-AIR-M3',
            'name' => '16GB / 512GB',
        ]);

        // Initial stock: 50 units
        $this->inventoryService->adjustStock($warehouse, $variant, 50, 'inflow', 'Boshlang\'ich kirim', $user);

        // Reserve 15 units for checkout
        $this->inventoryService->reserveStock($warehouse, $variant, 15, 'Buyurtma uchun band qilindi', $user);

        $freshBalance = InventoryBalance::where('warehouse_id', $warehouse->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        $this->assertEquals('50.000', $freshBalance->quantity);
        $this->assertEquals('15.000', $freshBalance->reserved_quantity);
        $this->assertEquals('35.000', $freshBalance->available());

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'reservation',
            'balance_before' => '0.000',
            'balance_after' => '15.000',
        ]);

        // Release 5 units
        $this->inventoryService->releaseStock($warehouse, $variant, 5, 'Vaqti o\'tgan rezerv bekor qilindi', $user);

        $freshBalanceAfterRelease = $freshBalance->fresh();
        $this->assertEquals('10.000', $freshBalanceAfterRelease->reserved_quantity);
        $this->assertEquals('40.000', $freshBalanceAfterRelease->available());

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'release',
            'balance_before' => '15.000',
            'balance_after' => '10.000',
        ]);
    }
}
