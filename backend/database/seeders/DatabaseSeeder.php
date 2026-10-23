<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            DemoCatalogSeeder::class,
            ExpandedCatalogSeeder::class,
            ProductImageSeeder::class,
            ProductReviewSeeder::class,
        ]);

        // Demo Customer
        $customer = User::firstOrCreate(
            ['email' => 'customer@market.uz'],
            [
                'name' => 'Dilshod Xaridor',
                'phone' => '+998909876543',
                'password' => Hash::make('Customer123!'),
                'is_active' => true,
            ]
        );
        $customer->assignRole('customer');

        // Demo Courier
        $courier = User::firstOrCreate(
            ['email' => 'courier@market.uz'],
            [
                'name' => 'Rustam Kuryer',
                'phone' => '+998905554433',
                'password' => Hash::make('Courier123!'),
                'is_active' => true,
            ]
        );
        $courier->assignRole('courier');

        // Demo Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@market.uz'],
            [
                'name' => 'Bosh Administrator',
                'phone' => '+998901112233',
                'password' => Hash::make('Admin123!'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');
    }
}
