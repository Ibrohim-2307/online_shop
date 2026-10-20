<?php

namespace Database\Seeders;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Suborder;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveTrackingSeeder extends Seeder
{
    /**
     * Seed a realistic active shipment with live courier GPS coordinates for the tracking map.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@market.uz')->first();
        $courier = User::where('email', 'courier@market.uz')->first();
        $seller = Seller::first();

        if (!$customer || !$courier || !$seller) {
            return;
        }

        // 1. Create or update Demo Active Order
        $order = Order::firstOrCreate(
            ['order_number' => 'ORD-20261020-001'],
            [
                'user_id' => $customer->id,
                'status' => 'processing',
                'currency' => 'UZS',
                'items_total' => 3520000,
                'shipping_total' => 45000,
                'grand_total' => 3565000,
                'shipping_address' => [
                    'recipient_name' => 'Dilshod Xaridor',
                    'phone' => '+998 90 987 65 43',
                    'region' => 'Toshkent shahri',
                    'city' => 'Mirzo Ulug\'bek tumani',
                    'address_line' => 'Mustaqillik shoh ko\'chasi, 105-uy, 3-xonadon',
                    'latitude' => 41.3111,
                    'longitude' => 69.2797,
                ],
                'notes' => 'Iltimos, yetib kelishdan 10 daqiqa oldin qo\'ng\'iroq qiling.',
                'created_at' => now()->subMinutes(45),
            ]
        );

        // 2. Create Suborder
        $suborder = Suborder::firstOrCreate(
            ['suborder_number' => 'SUB-20261020-001'],
            [
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'status' => 'shipped',
                'items_subtotal' => 3520000,
                'shipping_cost' => 45000,
                'total_amount' => 3565000,
                'commission_rate' => 5.0,
                'commission_amount' => 176000,
                'created_at' => now()->subMinutes(40),
            ]
        );

        // 3. Add order items if empty
        if ($suborder->items()->count() === 0) {
            $pDrywall = Product::where('slug', 'gipsokarton-knauf-95mm')->first();
            $vDrywall = $pDrywall?->variants->first();

            OrderItem::create([
                'suborder_id' => $suborder->id,
                'product_id' => $pDrywall?->id,
                'product_variant_id' => $vDrywall ? $vDrywall->id : 1,
                'product_name' => 'Gipsokarton Knauf 9.5mm (Namlikka chidamli)',
                'variant_sku' => $vDrywall ? $vDrywall->sku : 'KNAUF-GKB-95',
                'unit' => 'dona',
                'quantity' => 50,
                'unit_price' => 62000,
                'subtotal' => 3100000,
            ]);

            $pTile = Product::where('slug', 'kafel-yelimi-knauf-perlfix-25kg')->first();
            $vTile = $pTile?->variants->first();

            OrderItem::create([
                'suborder_id' => $suborder->id,
                'product_id' => $pTile?->id,
                'product_variant_id' => $vTile ? $vTile->id : 2,
                'product_name' => 'Kafel va Plitka Yelimi Knauf Perlfix 25kg',
                'variant_sku' => $vTile ? $vTile->sku : 'KNAUF-PERLFIX-25KG',
                'unit' => 'qop',
                'quantity' => 10,
                'unit_price' => 42000,
                'subtotal' => 420000,
            ]);
        }

        // 4. Create or update Shipment with realistic Tashkent coordinates
        $shipment = Shipment::updateOrCreate(
            ['tracking_number' => 'TRK-UZ-778899'],
            [
                'suborder_id' => $suborder->id,
                'courier_id' => $courier->id,
                'status' => 'in_transit',
                'shipping_method' => 'express_courier',
                'shipping_cost' => 45000,
                'pickup_address' => [
                    'title' => 'StroyBaza Sergeli Markaziy Ombori',
                    'address' => 'Toshkent sh., Sergeli sanoat zonasi, 14-bino',
                    'latitude' => 41.2285,
                    'longitude' => 69.2155,
                ],
                'delivery_address' => [
                    'recipient' => 'Dilshod Xaridor',
                    'phone' => '+998 90 987 65 43',
                    'address' => 'Mustaqillik shoh ko\'chasi, 105-uy, 3-xonadon',
                    'latitude' => 41.3111,
                    'longitude' => 69.2797,
                ],
                'estimated_delivery_at' => now()->addMinutes(25),
                'created_at' => now()->subMinutes(35),
            ]
        );

        // 5. Waypoints along the courier's route (Sergeli -> Yakkasaroy -> Mirzo Ulug'bek)
        $routePoints = [
            ['lat' => 41.2285, 'lng' => 69.2155, 'speed' => 0, 'heading' => 20, 'minutes_ago' => 30, 'note' => 'Ombordan yuklandi va jo\'natildi'],
            ['lat' => 41.2420, 'lng' => 69.2280, 'speed' => 45, 'heading' => 30, 'minutes_ago' => 22, 'note' => 'Sergeli ko\'prigi bo\'ylab harakatlanmoqda'],
            ['lat' => 41.2580, 'lng' => 69.2390, 'speed' => 38, 'heading' => 40, 'minutes_ago' => 15, 'note' => 'Janubiy vokzal hududidan o\'tmoqda'],
            ['lat' => 41.2720, 'lng' => 69.2530, 'speed' => 40, 'heading' => 35, 'minutes_ago' => 8, 'note' => 'Shota Rustaveli ko\'chasi bo\'ylab davom etmoqda'],
            ['lat' => 41.2890, 'lng' => 69.2660, 'speed' => 32, 'heading' => 25, 'minutes_ago' => 2, 'note' => 'Oybek metro chorrahasi yaqinida'],
            ['lat' => 41.2995, 'lng' => 69.2725, 'speed' => 35, 'heading' => 20, 'minutes_ago' => 0, 'note' => 'Amir Temur shoh ko\'chasida, manzilga 10-15 daqiqa qoldi'],
        ];

        foreach ($routePoints as $idx => $pt) {
            $eventId = 'GPS-TRK778899-' . $idx;
            CourierLocation::updateOrCreate(
                ['event_id' => $eventId],
                [
                    'courier_id' => $courier->id,
                    'shipment_id' => $shipment->id,
                    'latitude' => $pt['lat'],
                    'longitude' => $pt['lng'],
                    'speed' => $pt['speed'],
                    'heading' => $pt['heading'],
                    'accuracy' => 5.0,
                    'recorded_at' => now()->subMinutes($pt['minutes_ago']),
                    'received_at' => now()->subMinutes($pt['minutes_ago']),
                ]
            );

            ShipmentEvent::firstOrCreate(
                ['shipment_id' => $shipment->id, 'notes' => $pt['note']],
                [
                    'event_type' => $idx === 0 ? 'picked_up' : ($idx === count($routePoints) - 1 ? 'out_for_delivery' : 'location_ping'),
                    'payload' => ['latitude' => $pt['lat'], 'longitude' => $pt['lng'], 'speed' => $pt['speed']],
                    'created_at' => now()->subMinutes($pt['minutes_ago']),
                ]
            );
        }
    }
}
