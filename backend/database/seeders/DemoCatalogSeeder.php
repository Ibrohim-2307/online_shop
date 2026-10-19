<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Seed official demo catalog data conforming to TECHNICAL_SPEC.md.
     * Includes seller-configurable price tiers:
     * - 1 - 299 @ 300,000 UZS
     * - 300 - 999 @ 270,000 UZS
     * - 1000+ @ 250,000 UZS (1000 units = 250,000,000 UZS)
     * High value equipment:
     * - 1 - 9 @ 300,000,000 UZS
     * - 10+ @ 270,000,000 UZS
     */
    public function run(): void
    {
        // 1. Create or get Demo Seller Owner User
        $sellerUser = User::firstOrCreate(
            ['phone' => '+998901234567'],
            [
                'name' => 'Akbar Sotuvchi',
                'email' => 'seller@market.uz',
                'password' => Hash::make('Seller123!'),
                'is_active' => true,
            ]
        );
        $sellerUser->assignRole('seller_owner');

        // 2. Create Demo Seller Store
        $seller = Seller::firstOrCreate(
            ['slug' => 'stroybaza-ulgurji'],
            [
                'name' => 'StroyBaza Ulgurji Savdo',
                'legal_name' => 'StroyBaza Grand MCHJ',
                'tax_id' => '308123456',
                'status' => 'active',
                'commission_rate' => 5.00,
                'rating' => 4.90,
                'phone' => '+998901234567',
                'email' => 'contact@stroybaza.uz',
                'address' => 'Toshkent sh., Sergeli tumani, Yangi Sergeli ko\'chasi, 24-uy',
            ]
        );

        SellerMember::firstOrCreate(
            ['seller_id' => $seller->id, 'user_id' => $sellerUser->id],
            ['role' => 'owner', 'permissions' => ['*']]
        );

        // 3. Demo Categories
        $catConstruction = Category::firstOrCreate(
            ['slug' => 'qurilish-materiallari'],
            ['name' => 'Qurilish materiallari', 'sort_order' => 1, 'is_active' => true]
        );

        $catMachinery = Category::firstOrCreate(
            ['slug' => 'sanoat-uskunalari'],
            ['name' => 'Sanoat uskunalari', 'sort_order' => 2, 'is_active' => true]
        );

        $catFood = Category::firstOrCreate(
            ['slug' => 'oziq-ovqat-va-ichimliklar'],
            ['name' => 'Oziq-ovqat va ichimliklar', 'sort_order' => 3, 'is_active' => true]
        );

        // 4. Warehouse
        $warehouse = Warehouse::firstOrCreate(
            ['seller_id' => $seller->id, 'code' => 'WH-SERGELI-01'],
            [
                'name' => 'Asosiy Ombor (Sergeli)',
                'address' => 'Toshkent sh., Sergeli sanoat zonasi, 14-bino',
                'is_active' => true,
            ]
        );

        // -------------------------------------------------------------
        // Product 1: Cement M-500 (Example A from TECHNICAL_SPEC.md)
        // -------------------------------------------------------------
        $pCement = Product::firstOrCreate(
            ['slug' => 'sement-m500'],
            [
                'seller_id' => $seller->id,
                'category_id' => $catConstruction->id,
                'name' => 'Qurilish Sementi M-500',
                'description' => 'Yuqori sifatli portland sement M-500. Poydevor va monolit quyish uchun mo\'ljallangan.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.000,
                'min_order_quantity' => 1.000,
                'status' => 'approved',
                'is_active' => true,
            ]
        );

        $vCement = ProductVariant::firstOrCreate(
            ['sku' => 'CEMENT-M500-BAG'],
            [
                'product_id' => $pCement->id,
                'name' => '50 kg qop',
                'barcode' => '4780001234567',
                'attributes' => ['weight' => '50kg', 'brand' => 'Bekobod Sement'],
                'is_active' => true,
            ]
        );

        // Configure Seller Price Tiers (All-Units Model):
        // 1 - 299: 300,000 UZS
        // 300 - 999: 270,000 UZS
        // 1000+: 250,000 UZS (1000 units = 250,000,000 UZS)
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vCement->id, 'min_quantity' => 1.000],
            ['max_quantity' => 299.000, 'unit_price' => 300000, 'currency' => 'UZS']
        );
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vCement->id, 'min_quantity' => 300.000],
            ['max_quantity' => 999.000, 'unit_price' => 270000, 'currency' => 'UZS']
        );
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vCement->id, 'min_quantity' => 1000.000],
            ['max_quantity' => null, 'unit_price' => 250000, 'currency' => 'UZS']
        );

        // Stock: 50,000 bags
        $balCement = InventoryBalance::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $vCement->id],
            ['quantity' => 50000.000, 'reserved_quantity' => 0.000]
        );

        InventoryMovement::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $vCement->id, 'reference_type' => 'initial_seed'],
            [
                'type' => 'inflow',
                'quantity' => 50000.000,
                'balance_before' => 0.000,
                'balance_after' => 50000.000,
                'reason' => 'Boshlang\'ich ombor kirimi',
                'user_id' => $sellerUser->id,
            ]
        );

        // -------------------------------------------------------------
        // Product 2: Sanoat Generatori (Example B from TECHNICAL_SPEC.md)
        // -------------------------------------------------------------
        $pGenerator = Product::firstOrCreate(
            ['slug' => 'sanoat-generator-300kw'],
            [
                'seller_id' => $seller->id,
                'category_id' => $catMachinery->id,
                'name' => 'Sanoat Dizel Generatori 300 kW',
                'description' => 'Avtonom elektr ta\'minoti uchun yuqori quvvatli dizel generator stansiyasi.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.000,
                'min_order_quantity' => 1.000,
                'status' => 'approved',
                'is_active' => true,
            ]
        );

        $vGenerator = ProductVariant::firstOrCreate(
            ['sku' => 'GEN-IND-300M'],
            [
                'product_id' => $pGenerator->id,
                'name' => '300 kW Standart Komplektatsiya',
                'barcode' => '4780009876543',
                'attributes' => ['power' => '300kW', 'fuel' => 'Diesel'],
                'is_active' => true,
            ]
        );

        // Retail: 1-9 units @ 300,000,000 UZS
        // Wholesale: 10+ units @ 270,000,000 UZS
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vGenerator->id, 'min_quantity' => 1.000],
            ['max_quantity' => 9.000, 'unit_price' => 300000000, 'currency' => 'UZS']
        );
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vGenerator->id, 'min_quantity' => 10.000],
            ['max_quantity' => null, 'unit_price' => 270000000, 'currency' => 'UZS']
        );

        // Stock: 50 units
        InventoryBalance::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $vGenerator->id],
            ['quantity' => 50.000, 'reserved_quantity' => 0.000]
        );

        InventoryMovement::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $vGenerator->id, 'reference_type' => 'initial_seed'],
            [
                'type' => 'inflow',
                'quantity' => 50.000,
                'balance_before' => 0.000,
                'balance_after' => 50.000,
                'reason' => 'Zavoddan qabul qilingan generatorlar',
                'user_id' => $sellerUser->id,
            ]
        );

        // -------------------------------------------------------------
        // Product 3: Mineral Water (Box unit demonstration)
        // -------------------------------------------------------------
        $pWater = Product::firstOrCreate(
            ['slug' => 'mineral-suv-15l'],
            [
                'seller_id' => $seller->id,
                'category_id' => $catFood->id,
                'name' => 'Tog\' Chashmasi Mineral Suvi 1.5L',
                'description' => 'Gazlanmagan tabiiy tog\' mineral suvi, 12 dona butilka blokda.',
                'trade_mode' => 'both',
                'unit' => 'box',
                'items_per_box' => 12,
                'unit_step' => 1.000,
                'min_order_quantity' => 5.000,
                'status' => 'approved',
                'is_active' => true,
            ]
        );

        $vWater = ProductVariant::firstOrCreate(
            ['sku' => 'WATER-15L-BOX12'],
            [
                'product_id' => $pWater->id,
                'name' => '12 talik Blok',
                'barcode' => '4780005551122',
                'attributes' => ['volume' => '1.5L', 'packaging' => 'box'],
                'is_active' => true,
            ]
        );

        // 5 - 49 boxes @ 48,000 UZS / box
        // 50+ boxes @ 42,000 UZS / box
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vWater->id, 'min_quantity' => 5.000],
            ['max_quantity' => 49.000, 'unit_price' => 48000, 'currency' => 'UZS']
        );
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vWater->id, 'min_quantity' => 50.000],
            ['max_quantity' => null, 'unit_price' => 42000, 'currency' => 'UZS']
        );

        InventoryBalance::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $vWater->id],
            ['quantity' => 2000.000, 'reserved_quantity' => 0.000]
        );

        // -------------------------------------------------------------
        // Product 4: Steel Rebar (Ton unit demonstration)
        // -------------------------------------------------------------
        $pRebar = Product::firstOrCreate(
            ['slug' => 'armatura-a500c'],
            [
                'seller_id' => $seller->id,
                'category_id' => $catConstruction->id,
                'name' => 'Po\'lat Armatura A500C 12mm',
                'description' => 'Issiq prokatlangan po\'lat armatura, davlat standarti GOST 52544.',
                'trade_mode' => 'wholesale_only',
                'unit' => 'ton',
                'unit_step' => 0.500,
                'min_order_quantity' => 1.000,
                'status' => 'approved',
                'is_active' => true,
            ]
        );

        $vRebar = ProductVariant::firstOrCreate(
            ['sku' => 'ARM-A500C-12MM-TON'],
            [
                'product_id' => $pRebar->id,
                'name' => '12mm tonna',
                'barcode' => '4780007778899',
                'attributes' => ['diameter' => '12mm', 'grade' => 'A500C'],
                'is_active' => true,
            ]
        );

        // 1 - 9.5 tons @ 9,500,000 UZS / ton
        // 10+ tons @ 8,900,000 UZS / ton
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vRebar->id, 'min_quantity' => 1.000],
            ['max_quantity' => 9.500, 'unit_price' => 9500000, 'currency' => 'UZS']
        );
        PriceTier::updateOrCreate(
            ['product_variant_id' => $vRebar->id, 'min_quantity' => 10.000],
            ['max_quantity' => null, 'unit_price' => 8900000, 'currency' => 'UZS']
        );

        InventoryBalance::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $vRebar->id],
            ['quantity' => 350.000, 'reserved_quantity' => 0.000]
        );
    }
}
