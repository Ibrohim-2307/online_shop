<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerProfileSeeder extends Seeder
{
    /**
     * Seed initial customer addresses and profile data.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@market.uz')->first();
        if ($customer) {
            Address::firstOrCreate(
                [
                    'user_id' => $customer->id,
                    'title' => 'Uy manzili (Asosiy)',
                ],
                [
                    'recipient_name' => 'Dilshod Xaridor',
                    'phone' => '+998 90 987 65 43',
                    'full_address' => 'Toshkent shahri, Mirzo Ulug\'bek tumani, Mustaqillik shoh ko\'chasi, 105-uy, 3-xonadon',
                    'landmark' => 'Eski konservatoriya va Mirzo Ulug\'bek bog\'i ro\'parasi',
                    'latitude' => 41.3111,
                    'longitude' => 69.2797,
                    'is_default' => true,
                ]
            );

            Address::firstOrCreate(
                [
                    'user_id' => $customer->id,
                    'title' => 'Qurilish ob\'ekti / Ishxona',
                ],
                [
                    'recipient_name' => 'Dilshod Xaridor (Usta Shavkat orqali)',
                    'phone' => '+998 97 765 43 21',
                    'full_address' => 'Toshkent shahri, Yunusobod tumani, Amir Temur shoh ko\'chasi, 45-bino',
                    'landmark' => 'Shahriston metrosi yaqinida, yangi turar-joy majmuasi',
                    'latitude' => 41.3520,
                    'longitude' => 69.2890,
                    'is_default' => false,
                ]
            );
        }

        $seller = User::where('email', 'seller@market.uz')->first();
        if ($seller) {
            Address::firstOrCreate(
                [
                    'user_id' => $seller->id,
                    'title' => 'Markaziy Omborxona',
                ],
                [
                    'recipient_name' => 'Akbar Sotuvchi (StroyBaza)',
                    'phone' => '+998 90 123 45 67',
                    'full_address' => 'Toshkent sh., Sergeli tumani, Sergeli sanoat zonasi, 14-bino, 2-omborxona',
                    'landmark' => 'Sergeli avtomobil bozori orqasidagi logistika markazi',
                    'latitude' => 41.2285,
                    'longitude' => 69.2155,
                    'is_default' => true,
                ]
            );
        }
    }
}
