<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ExpandedCatalogSeeder extends Seeder
{
    /**
     * Seed 15+ realistic wholesale and retail products.
     */
    public function run(): void
    {
        $seller = Seller::where('slug', 'stroybaza-ulgurji')->first();
        if (!$seller) {
            $seller = Seller::first();
        }

        $warehouse = Warehouse::where('seller_id', $seller->id)->first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'seller_id' => $seller->id,
                'name' => 'Asosiy Ombor (Sergeli)',
                'code' => 'WH-SERGELI-01',
                'address' => 'Toshkent sh., Sergeli sanoat zonasi, 14-bino',
                'is_active' => true,
            ]);
        }

        // Categories
        $categories = [
            'qurilish-materiallari' => Category::firstOrCreate(['slug' => 'qurilish-materiallari'], ['name' => 'Qurilish materiallari', 'sort_order' => 1, 'is_active' => true]),
            'sanoat-uskunalari' => Category::firstOrCreate(['slug' => 'sanoat-uskunalari'], ['name' => 'Sanoat uskunalari', 'sort_order' => 2, 'is_active' => true]),
            'oziq-ovqat-va-ichimliklar' => Category::firstOrCreate(['slug' => 'oziq-ovqat-va-ichimliklar'], ['name' => 'Oziq-ovqat va ichimliklar', 'sort_order' => 3, 'is_active' => true]),
            'elektr-jihozlari' => Category::firstOrCreate(['slug' => 'elektr-jihozlari'], ['name' => 'Elektr va yoritish jihozlari', 'sort_order' => 4, 'is_active' => true]),
            'boyoqlar-va-kimyo' => Category::firstOrCreate(['slug' => 'boyoqlar-va-kimyo'], ['name' => 'Bo\'yoqlar va kimyoviy mahsulotlar', 'sort_order' => 5, 'is_active' => true]),
            'santexnika-va-quvurlar' => Category::firstOrCreate(['slug' => 'santexnika-va-quvurlar'], ['name' => 'Santexnika va quvurlar', 'sort_order' => 6, 'is_active' => true]),
            'asbob-uskunalar' => Category::firstOrCreate(['slug' => 'asbob-uskunalar'], ['name' => 'Qurilish asbob-uskunalari', 'sort_order' => 7, 'is_active' => true]),
        ];

        $items = [
            // 1. Gipsokarton
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Gipsokarton Knauf 9.5mm (Namlikka chidamli)',
                'slug' => 'gipsokarton-knauf-95mm',
                'description' => 'Knauf sifatli yashil namlikka chidamli gipsokarton plitasi. O\'lchami: 1200x2500x9.5 mm.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 5.0,
                'variants' => [
                    [
                        'sku' => 'KNAUF-GKB-95-2500',
                        'name' => '9.5mm 1.2x2.5m Yashil',
                        'barcode' => '4780009112233',
                        'attributes' => ['thickness' => '9.5mm', 'size' => '1.2x2.5m', 'type' => 'Moisture resistant'],
                        'stock' => 1200.0,
                        'tiers' => [
                            ['min' => 5.0, 'max' => 49.0, 'price' => 68000],
                            ['min' => 50.0, 'max' => 199.0, 'price' => 62000],
                            ['min' => 200.0, 'max' => null, 'price' => 57000],
                        ]
                    ]
                ]
            ],
            // 2. Pishiq g'isht
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Pishiq qizil g\'isht M-125 (Standart)',
                'slug' => 'pishiq-qizil-gisht-m125',
                'description' => 'Devor va poydevor ko\'tarish uchun mo\'ljallangan standart pishiq qizil g\'isht M-125.',
                'trade_mode' => 'wholesale_only',
                'unit' => 'piece',
                'unit_step' => 500.0,
                'min_order_quantity' => 1000.0,
                'variants' => [
                    [
                        'sku' => 'BRICK-RED-M125',
                        'name' => 'M-125 Qizil donali',
                        'barcode' => '4780001002003',
                        'attributes' => ['brand' => 'Yangiyo\'l G\'isht Zavodi', 'grade' => 'M-125'],
                        'stock' => 250000.0,
                        'tiers' => [
                            ['min' => 1000.0, 'max' => 4999.0, 'price' => 1800],
                            ['min' => 5000.0, 'max' => 19999.0, 'price' => 1650],
                            ['min' => 20000.0, 'max' => null, 'price' => 1500],
                        ]
                    ]
                ]
            ],
            // 3. Shlakoblok
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Shlakoblok Devorbop 20x20x40',
                'slug' => 'shlakoblok-devorbop-20x20x40',
                'description' => 'Yuqori bosimda presslangan standart shlakoblok. Mustahkam va issiqlikni yaxshi saqlaydi.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 50.0,
                'min_order_quantity' => 50.0,
                'variants' => [
                    [
                        'sku' => 'SHLAK-20-20-40',
                        'name' => '20x20x40 sm standart',
                        'barcode' => '4780003004005',
                        'attributes' => ['dimensions' => '20x20x40cm', 'weight' => '16kg'],
                        'stock' => 15000.0,
                        'tiers' => [
                            ['min' => 50.0, 'max' => 499.0, 'price' => 5500],
                            ['min' => 500.0, 'max' => 1999.0, 'price' => 4900],
                            ['min' => 2000.0, 'max' => null, 'price' => 4400],
                        ]
                    ]
                ]
            ],
            // 4. Kafel yelimi Knauf Perlfix
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Kafel va Plitka Yelimi Knauf Perlfix 25kg',
                'slug' => 'kafel-yelimi-knauf-perlfix-25kg',
                'description' => 'Ichki va tashqi pardozlash ishlari uchun yuqori yopishqoqlikdagi kafel yelimi.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 2.0,
                'variants' => [
                    [
                        'sku' => 'KNAUF-PERLFIX-25KG',
                        'name' => '25 kg qop',
                        'barcode' => '4780005006007',
                        'attributes' => ['weight' => '25kg', 'brand' => 'Knauf'],
                        'stock' => 3500.0,
                        'tiers' => [
                            ['min' => 2.0, 'max' => 49.0, 'price' => 48000],
                            ['min' => 50.0, 'max' => 199.0, 'price' => 42000],
                            ['min' => 200.0, 'max' => null, 'price' => 37000],
                        ]
                    ]
                ]
            ],
            // 5. Akril fasad bo'yog'i MasterColor
            [
                'category' => 'boyoqlar-va-kimyo',
                'name' => 'Akril Fasad Bo\'yog\'i MasterColor 20kg',
                'slug' => 'akril-fasad-boyogi-mastercolor-20kg',
                'description' => 'Ob-havo ta\'siriga chidamli oq fasad bo\'yog\'i. Suv o\'tkazmaydi, quyoshda so\'nmaydi.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'variants' => [
                    [
                        'sku' => 'PAINT-MC-FACADE-20KG',
                        'name' => '20 kg plastik chelak',
                        'barcode' => '4780007008009',
                        'attributes' => ['color' => 'White', 'volume' => '20kg', 'base' => 'Acrylic'],
                        'stock' => 850.0,
                        'tiers' => [
                            ['min' => 1.0, 'max' => 9.0, 'price' => 320000],
                            ['min' => 10.0, 'max' => 49.0, 'price' => 285000],
                            ['min' => 50.0, 'max' => null, 'price' => 250000],
                        ]
                    ]
                ]
            ],
            // 6. Elektr kabeli VVG-P 3x2.5
            [
                'category' => 'elektr-jihozlari',
                'name' => 'Mis Elektr Kabeli VVG-P 3x2.5 (100m buxta)',
                'slug' => 'mis-elektr-kabeli-vvg-p-3x25',
                'description' => 'Davlat standarti (GOST) bo\'yicha toza misdan tayyorlangan kuchlanish kabeli 3x2.5 mm.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'variants' => [
                    [
                        'sku' => 'CABLE-VVGP-3X25-100M',
                        'name' => '100 metr buxta',
                        'barcode' => '4780009001011',
                        'attributes' => ['conductor' => 'Copper', 'core' => '3x2.5', 'length' => '100m'],
                        'stock' => 450.0,
                        'tiers' => [
                            ['min' => 1.0, 'max' => 9.0, 'price' => 850000],
                            ['min' => 10.0, 'max' => 49.0, 'price' => 780000],
                            ['min' => 50.0, 'max' => null, 'price' => 720000],
                        ]
                    ]
                ]
            ],
            // 7. LED Sanoat Projektori 200W
            [
                'category' => 'elektr-jihozlari',
                'name' => 'LED Sanoat Projektori 200W IP66 (Sovuq oq)',
                'slug' => 'led-sanoat-projektori-200w-ip66',
                'description' => 'Omborlar, qurilish maydonlari va stadionlar uchun quvvatli LED projektor. Suv va changdan to\'liq himoyalangan.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'variants' => [
                    [
                        'sku' => 'LED-FLOOD-200W-IP66',
                        'name' => '200W IP66 6500K',
                        'barcode' => '4780009002022',
                        'attributes' => ['power' => '200W', 'protection' => 'IP66', 'light' => '6500K'],
                        'stock' => 600.0,
                        'tiers' => [
                            ['min' => 1.0, 'max' => 9.0, 'price' => 340000],
                            ['min' => 10.0, 'max' => 49.0, 'price' => 295000],
                            ['min' => 50.0, 'max' => null, 'price' => 260000],
                        ]
                    ]
                ]
            ],
            // 8. Bazalt mineral paxta
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Bazalt Mineral Paxta TeploIzol 50mm',
                'slug' => 'bazalt-mineral-paxta-teploizol-50mm',
                'description' => 'Uy tomi, devorlari va pollarni issiqlik va shovqindan izolyatsiya qilish uchun mineral vata.',
                'trade_mode' => 'both',
                'unit' => 'box',
                'unit_step' => 1.0,
                'min_order_quantity' => 5.0,
                'variants' => [
                    [
                        'sku' => 'BASALT-WOOL-50MM-PACK',
                        'name' => '50mm 6 kv.m o\'ram',
                        'barcode' => '4780009003033',
                        'attributes' => ['thickness' => '50mm', 'coverage' => '6m2', 'density' => '35kg/m3'],
                        'stock' => 1800.0,
                        'tiers' => [
                            ['min' => 5.0, 'max' => 49.0, 'price' => 185000],
                            ['min' => 50.0, 'max' => 199.0, 'price' => 165000],
                            ['min' => 200.0, 'max' => null, 'price' => 148000],
                        ]
                    ]
                ]
            ],
            // 9. Plastik quvur PN20 D25mm
            [
                'category' => 'santexnika-va-quvurlar',
                'name' => 'Polipropilen Issiq Suv Quvuri PN20 D25mm (4m)',
                'slug' => 'polipropilen-quvur-pn20-d25mm-4m',
                'description' => 'Issiq va sovuq suv tarmoqlari hamda isitish tizimlari uchun shisha tolali mustahkamlangan quvur.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 5.0,
                'min_order_quantity' => 10.0,
                'variants' => [
                    [
                        'sku' => 'PIPE-PPR-PN20-D25-4M',
                        'name' => 'D25mm 4 metr',
                        'barcode' => '4780009004044',
                        'attributes' => ['diameter' => '25mm', 'length' => '4m', 'pressure' => 'PN20'],
                        'stock' => 8000.0,
                        'tiers' => [
                            ['min' => 10.0, 'max' => 99.0, 'price' => 28000],
                            ['min' => 100.0, 'max' => 499.0, 'price' => 24000],
                            ['min' => 500.0, 'max' => null, 'price' => 21000],
                        ]
                    ]
                ]
            ],
            // 10. Samarqand bug'doy uni Oltin Don 50kg
            [
                'category' => 'oziq-ovqat-va-ichimliklar',
                'name' => 'Samarqand Bug\'doy Uni "Oltin Don" 1-nav 50kg',
                'slug' => 'samarqand-bugdoy-uni-oltin-don-50kg',
                'description' => 'Nonvoyxonalar va qandolatchilik korxonalari uchun yuqori navli sifatli bug\'doy uni.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 2.0,
                'variants' => [
                    [
                        'sku' => 'FLOUR-OLTIN-DON-50KG',
                        'name' => '50 kg qop 1-nav',
                        'barcode' => '4780009005055',
                        'attributes' => ['weight' => '50kg', 'grade' => '1st Grade', 'origin' => 'Samarqand'],
                        'stock' => 4200.0,
                        'tiers' => [
                            ['min' => 2.0, 'max' => 49.0, 'price' => 310000],
                            ['min' => 50.0, 'max' => 199.0, 'price' => 280000],
                            ['min' => 200.0, 'max' => null, 'price' => 260000],
                        ]
                    ]
                ]
            ],
            // 11. Paxta yog'i Zilol 5L
            [
                'category' => 'oziq-ovqat-va-ichimliklar',
                'name' => 'Paxta Yog\'i "Zilol" Rafinatsiyalangan 5L (Quti = 4 dona)',
                'slug' => 'paxta-yogi-zilol-5l-quti',
                'description' => 'Tozalangan va xidsizlantirilgan yuqori sifatli tabiiy paxta yog\'i.',
                'trade_mode' => 'both',
                'unit' => 'box',
                'unit_step' => 1.0,
                'min_order_quantity' => 2.0,
                'variants' => [
                    [
                        'sku' => 'OIL-ZILOL-5L-BOX4',
                        'name' => 'Qutida 4x5L (20L jami)',
                        'barcode' => '4780009006066',
                        'attributes' => ['bottle' => '5L', 'packaging' => '4 bottles / box'],
                        'stock' => 1400.0,
                        'tiers' => [
                            ['min' => 2.0, 'max' => 19.0, 'price' => 340000],
                            ['min' => 20.0, 'max' => 99.0, 'price' => 310000],
                            ['min' => 100.0, 'max' => null, 'price' => 285000],
                        ]
                    ]
                ]
            ],
            // 12. Lazerli nivelir 4D
            [
                'category' => 'asbob-uskunalar',
                'name' => 'Lazerli Nivelir 4D 16 Liniyali "ProLaser 360"',
                'slug' => 'lazerli-nivelir-4d-16-liniyali-prolaser',
                'description' => 'Yashil nurli professional 360 daraja 16 liniyali lazer sathi. Pult va shtativ bilan birga.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'variants' => [
                    [
                        'sku' => 'LASER-4D-16LINE-SET',
                        'name' => 'To\'liq komplekt + 2 ta akkumulyator',
                        'barcode' => '4780009007077',
                        'attributes' => ['beam' => 'Green 532nm', 'accuracy' => '±1mm/7m', 'lines' => '16'],
                        'stock' => 180.0,
                        'tiers' => [
                            ['min' => 1.0, 'max' => 4.0, 'price' => 1250000],
                            ['min' => 5.0, 'max' => 19.0, 'price' => 1050000],
                            ['min' => 20.0, 'max' => null, 'price' => 920000],
                        ]
                    ]
                ]
            ],
            // 13. Payvandlash apparati Invertor IGBT 250A
            [
                'category' => 'sanoat-uskunalari',
                'name' => 'Payvandlash Apparati Invertor IGBT 250A "WeldPro"',
                'slug' => 'payvandlash-apparati-invertor-igbt-250a',
                'description' => 'Elektr kuchlanishi pasayganda ham barqaror ishlaydigan professional payvandlash uskunasi.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'variants' => [
                    [
                        'sku' => 'WELD-IGBT-250A-PRO',
                        'name' => '250A Raqamli displey bilan',
                        'barcode' => '4780009008088',
                        'attributes' => ['current' => '20-250A', 'electrode' => '1.6-5.0mm', 'technology' => 'IGBT'],
                        'stock' => 120.0,
                        'tiers' => [
                            ['min' => 1.0, 'max' => 4.0, 'price' => 1850000],
                            ['min' => 5.0, 'max' => 19.0, 'price' => 1620000],
                            ['min' => 20.0, 'max' => null, 'price' => 1450000],
                        ]
                    ]
                ]
            ],
            // 14. Granit plitasi 60x60 sm
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Kerapogranit Plitasi 60x60 sm (Quti = 1.44 m2)',
                'slug' => 'keramogranit-plitasi-60x60-sm',
                'description' => 'Marmar to\'qimali jilolangan yuqori darajada mustahkam keramogranit pol plitasi.',
                'trade_mode' => 'both',
                'unit' => 'box',
                'unit_step' => 1.0,
                'min_order_quantity' => 5.0,
                'variants' => [
                    [
                        'sku' => 'GRANITE-60X60-BOX',
                        'name' => '1.44 kv.m (4 dona plita / quti)',
                        'barcode' => '4780009009099',
                        'attributes' => ['size' => '60x60cm', 'surface' => 'Polished', 'box_area' => '1.44m2'],
                        'stock' => 2200.0,
                        'tiers' => [
                            ['min' => 5.0, 'max' => 49.0, 'price' => 165000],
                            ['min' => 50.0, 'max' => 199.0, 'price' => 145000],
                            ['min' => 200.0, 'max' => null, 'price' => 130000],
                        ]
                    ]
                ]
            ],
            // 15. Metall Profil Truba 40x20x2mm
            [
                'category' => 'qurilish-materiallari',
                'name' => 'Po\'lat Profil Truba 40x20x2mm (6 metr)',
                'slug' => 'polat-profil-truba-40x20x2mm-6m',
                'description' => 'Darvoza, to\'siq va karkas konstruksiyalari uchun to\'g\'ri burchakli profil trubasi.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 5.0,
                'variants' => [
                    [
                        'sku' => 'PROFILE-PIPE-40X20-6M',
                        'name' => '40x20 mm 6 metr donali',
                        'barcode' => '4780009010100',
                        'attributes' => ['dimensions' => '40x20mm', 'thickness' => '2mm', 'length' => '6m'],
                        'stock' => 5500.0,
                        'tiers' => [
                            ['min' => 5.0, 'max' => 49.0, 'price' => 95000],
                            ['min' => 50.0, 'max' => 199.0, 'price' => 86000],
                            ['min' => 200.0, 'max' => null, 'price' => 78000],
                        ]
                    ]
                ]
            ],
            // 16. Sanoat Kompressori 100L
            [
                'category' => 'sanoat-uskunalari',
                'name' => 'Porshenli Havo Kompressori 100L "AirPower 3HP"',
                'slug' => 'porshenli-havo-kompressori-100l',
                'description' => 'Avtoservis va ishlab chiqarish ustaxonalari uchun 8 bar bosimli 3 ot kuchiga ega havo kompressori.',
                'trade_mode' => 'both',
                'unit' => 'piece',
                'unit_step' => 1.0,
                'min_order_quantity' => 1.0,
                'variants' => [
                    [
                        'sku' => 'COMPRESSOR-100L-3HP',
                        'name' => '100 Litr 8 bar 2.2kW',
                        'barcode' => '4780009011111',
                        'attributes' => ['tank' => '100L', 'power' => '3HP (2.2kW)', 'pressure' => '8 bar'],
                        'stock' => 85.0,
                        'tiers' => [
                            ['min' => 1.0, 'max' => 3.0, 'price' => 4200000],
                            ['min' => 4.0, 'max' => 9.0, 'price' => 3750000],
                            ['min' => 10.0, 'max' => null, 'price' => 3400000],
                        ]
                    ]
                ]
            ],
        ];

        foreach ($items as $item) {
            $cat = $categories[$item['category']] ?? $categories['qurilish-materiallari'];
            
            $product = Product::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'seller_id' => $seller->id,
                    'category_id' => $cat->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'trade_mode' => $item['trade_mode'],
                    'unit' => $item['unit'],
                    'unit_step' => $item['unit_step'],
                    'min_order_quantity' => $item['min_order_quantity'],
                    'status' => 'approved',
                    'is_active' => true,
                ]
            );

            foreach ($item['variants'] as $vData) {
                $variant = ProductVariant::firstOrCreate(
                    ['sku' => $vData['sku']],
                    [
                        'product_id' => $product->id,
                        'name' => $vData['name'],
                        'barcode' => $vData['barcode'],
                        'attributes' => $vData['attributes'],
                        'is_active' => true,
                    ]
                );

                // Tiered pricing
                foreach ($vData['tiers'] as $t) {
                    PriceTier::updateOrCreate(
                        [
                            'product_variant_id' => $variant->id,
                            'min_quantity' => $t['min'],
                        ],
                        [
                            'max_quantity' => $t['max'],
                            'unit_price' => $t['price'],
                            'currency' => 'UZS',
                        ]
                    );
                }

                // Inventory balance
                InventoryBalance::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'quantity' => $vData['stock'],
                        'reserved_quantity' => 0.0,
                    ]
                );

                // Inventory movement
                InventoryMovement::firstOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'product_variant_id' => $variant->id,
                        'reference_type' => 'initial_seed',
                    ],
                    [
                        'type' => 'inflow',
                        'quantity' => $vData['stock'],
                        'balance_before' => 0.0,
                        'balance_after' => $vData['stock'],
                        'reason' => 'Dastlabki ombor zaxirasini yuklash',
                        'reference_id' => 1,
                    ]
                );
            }
        }
    }
}
