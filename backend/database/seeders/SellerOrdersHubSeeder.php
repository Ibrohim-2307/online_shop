<?php

namespace Database\Seeders;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Suborder;
use App\Models\User;
use Illuminate\Database\Seeder;

class SellerOrdersHubSeeder extends Seeder
{
    /**
     * Seed both Inbound Purchases (Sotuvchining o'zi xarid qilgan tovarlari)
     * and Outbound Customer Sales (Mijozlar bergan buyurtmalar).
     */
    public function run(): void
    {
        $sellerUser = User::where('email', 'seller@market.uz')->first();
        $customerUser = User::where('email', 'customer@market.uz')->first();
        $courierUser = User::where('email', 'courier@market.uz')->first();
        $sellerStroyBaza = Seller::first();

        if (!$sellerUser || !$customerUser || !$courierUser || !$sellerStroyBaza) {
            return;
        }

        // 1. Create a Primary Supplier / Manufacturer Seller
        $supplier = Seller::firstOrCreate(
            ['slug' => 'uz-stroy-holding-zavod'],
            [
                'name' => 'O\'zbekiston Qurilish Xolding Zavodi (Bosh Distribyutor)',
                'legal_name' => 'UzStroy Holding Distribution XK',
                'tax_id' => '309988776',
                'status' => 'active',
                'commission_rate' => 3.5,
                'rating' => 4.95,
            ]
        );

        // 2. ORDER #1: Seller Purchased from Supplier (Mening Xaridim / Inbound Procurement)
        // Akbar Sotuvchi buys 100 gipsokarton and 50 shpatlyovka for his store
        $procurementOrder = Order::firstOrCreate(
            ['order_number' => 'ORD-B2B-20261020-099'],
            [
                'user_id' => $sellerUser->id,
                'status' => 'processing',
                'currency' => 'UZS',
                'items_total' => 12400000,
                'shipping_total' => 150000,
                'grand_total' => 12550000,
                'shipping_address' => [
                    'recipient_name' => 'Akbar Sotuvchi (StroyBaza Ombori)',
                    'phone' => '+998 90 123 45 67',
                    'region' => 'Toshkent shahri',
                    'city' => 'Sergeli tumani',
                    'address_line' => 'Sergeli sanoat zonasi, StroyBaza 14-bino, 2-omborxona',
                    'latitude' => 41.2285,
                    'longitude' => 69.2155,
                ],
                'notes' => 'Katta og\'ir yuk mashinasida tushirish uchun kran mavjud.',
                'created_at' => now()->subHours(2),
            ]
        );

        $procurementSuborder = Suborder::firstOrCreate(
            ['suborder_number' => 'SUB-B2B-20261020-099'],
            [
                'order_id' => $procurementOrder->id,
                'seller_id' => $supplier->id,
                'status' => 'shipped',
                'items_subtotal' => 12400000,
                'shipping_cost' => 150000,
                'total_amount' => 12550000,
                'commission_rate' => 3.5,
                'commission_amount' => 434000,
                'created_at' => now()->subHours(2),
            ]
        );

        if ($procurementSuborder->items()->count() === 0) {
            $pDrywall = Product::where('slug', 'gipsokarton-knauf-95mm')->first();
            $vDrywall = $pDrywall?->variants->first();

            OrderItem::create([
                'suborder_id' => $procurementSuborder->id,
                'product_id' => $pDrywall?->id ?? 1,
                'product_variant_id' => $vDrywall?->id ?? 1,
                'product_name' => 'Knauf Gipsokarton 9.5mm (Zavod ulgurji partiya)',
                'variant_sku' => 'ZAVOD-KNAUF-100',
                'unit' => 'dona',
                'quantity' => 100,
                'unit_price' => 58000,
                'subtotal' => 5800000,
            ]);

            $pTile = Product::where('slug', 'kafel-yelimi-knauf-perlfix-25kg')->first();
            $vTile = $pTile?->variants->first();

            OrderItem::create([
                'suborder_id' => $procurementSuborder->id,
                'product_id' => $pTile?->id ?? 2,
                'product_variant_id' => $vTile?->id ?? 2,
                'product_name' => 'Knauf Perlfix Kafel Yelimi 25kg (Zavod poddon)',
                'variant_sku' => 'ZAVOD-PERLFIX-50',
                'unit' => 'qop',
                'quantity' => 80,
                'unit_price' => 38000,
                'subtotal' => 3040000,
            ]);

            OrderItem::create([
                'suborder_id' => $procurementSuborder->id,
                'product_id' => 3,
                'product_variant_id' => 3,
                'product_name' => 'Metal Qurilish Profili CD-60 4 metrli (Zavod bog\'lam)',
                'variant_sku' => 'METAL-CD60-200',
                'unit' => 'dona',
                'quantity' => 120,
                'unit_price' => 29666,
                'subtotal' => 3560000,
            ]);
        }

        // Active Shipment for Inbound Procurement (Being Delivered to Akbar's Warehouse)
        $procurementShipment = Shipment::updateOrCreate(
            ['tracking_number' => 'TRK-UZ-889900'],
            [
                'suborder_id' => $procurementSuborder->id,
                'courier_id' => $courierUser->id,
                'status' => 'in_transit',
                'shipping_method' => 'heavy_freight',
                'shipping_cost' => 150000,
                'pickup_address' => [
                    'title' => 'O\'zStroy Xolding Bosh Zavod Ombori',
                    'address' => 'Toshkent viloyati, Yangiyo\'l tumani, Zavod ko\'chasi, 1-zona',
                    'latitude' => 41.1350,
                    'longitude' => 69.0550,
                ],
                'delivery_address' => [
                    'recipient' => 'Akbar Sotuvchi (StroyBaza)',
                    'phone' => '+998 90 123 45 67',
                    'address' => 'Toshkent sh., Sergeli sanoat zonasi, 14-bino, 2-omborxona',
                    'latitude' => 41.2285,
                    'longitude' => 69.2155,
                ],
                'estimated_delivery_at' => now()->addMinutes(45),
                'created_at' => now()->subHours(2),
            ]
        );

        // Telemetry points along Yangiyo'l -> Sergeli route
        $pWaypoints = [
            ['lat' => 41.1350, 'lng' => 69.0550, 'speed' => 0, 'minutes_ago' => 90, 'note' => 'Zavod omboridan Isuzu yuk mashinasiga ortildi'],
            ['lat' => 41.1620, 'lng' => 69.0950, 'speed' => 52, 'minutes_ago' => 60, 'note' => 'M-39 trassasi bo\'ylab harakatlanmoqda'],
            ['lat' => 41.1910, 'lng' => 69.1550, 'speed' => 48, 'minutes_ago' => 30, 'note' => 'Ersgali aylanma yo\'lidan o\'tmoqda'],
            ['lat' => 41.2150, 'lng' => 69.1950, 'speed' => 38, 'minutes_ago' => 10, 'note' => 'Sergeli sanoat zonasiga kirdi, 15-20 daqiqa qoldi'],
        ];

        foreach ($pWaypoints as $i => $pt) {
            CourierLocation::updateOrCreate(
                ['event_id' => 'GPS-TRK889900-' . $i],
                [
                    'courier_id' => $courierUser->id,
                    'shipment_id' => $procurementShipment->id,
                    'latitude' => $pt['lat'],
                    'longitude' => $pt['lng'],
                    'speed' => $pt['speed'],
                    'heading' => 45,
                    'accuracy' => 4.5,
                    'recorded_at' => now()->subMinutes($pt['minutes_ago']),
                    'received_at' => now()->subMinutes($pt['minutes_ago']),
                ]
            );

            ShipmentEvent::firstOrCreate(
                ['shipment_id' => $procurementShipment->id, 'notes' => $pt['note']],
                [
                    'event_type' => $i === 0 ? 'picked_up' : 'in_transit',
                    'payload' => ['lat' => $pt['lat'], 'lng' => $pt['lng'], 'speed' => $pt['speed']],
                    'created_at' => now()->subMinutes($pt['minutes_ago']),
                ]
            );
        }

        // 3. ORDER #2: Historical Completed Purchase by Akbar Sotuvchi
        $deliveredOrder = Order::firstOrCreate(
            ['order_number' => 'ORD-B2B-20261015-012'],
            [
                'user_id' => $sellerUser->id,
                'status' => 'completed',
                'currency' => 'UZS',
                'items_total' => 7800000,
                'shipping_total' => 100000,
                'grand_total' => 7900000,
                'shipping_address' => [
                    'recipient_name' => 'Akbar Sotuvchi',
                    'phone' => '+998 90 123 45 67',
                    'region' => 'Toshkent shahri',
                    'city' => 'Sergeli tumani',
                    'address_line' => 'Sergeli sanoat zonasi, StroyBaza 14-bino',
                    'latitude' => 41.2285,
                    'longitude' => 69.2155,
                ],
                'created_at' => now()->subDays(5),
            ]
        );

        $deliveredSuborder = Suborder::firstOrCreate(
            ['suborder_number' => 'SUB-B2B-20261015-012'],
            [
                'order_id' => $deliveredOrder->id,
                'seller_id' => $supplier->id,
                'status' => 'delivered',
                'items_subtotal' => 7800000,
                'shipping_cost' => 100000,
                'total_amount' => 7900000,
                'commission_rate' => 3.5,
                'commission_amount' => 273000,
                'created_at' => now()->subDays(5),
            ]
        );

        if ($deliveredSuborder->items()->count() === 0) {
            OrderItem::create([
                'suborder_id' => $deliveredSuborder->id,
                'product_id' => 1,
                'product_variant_id' => 1,
                'product_name' => 'Sement M-500 Ohangaron (Zavod 100 qop)',
                'variant_sku' => 'OHANGARON-M500-100',
                'unit' => 'qop',
                'quantity' => 100,
                'unit_price' => 78000,
                'subtotal' => 7800000,
            ]);
        }

        Shipment::updateOrCreate(
            ['tracking_number' => 'TRK-UZ-554433'],
            [
                'suborder_id' => $deliveredSuborder->id,
                'courier_id' => $courierUser->id,
                'status' => 'delivered',
                'shipping_method' => 'heavy_freight',
                'shipping_cost' => 100000,
                'delivered_at' => now()->subDays(4),
                'created_at' => now()->subDays(5),
            ]
        );
    }
}
