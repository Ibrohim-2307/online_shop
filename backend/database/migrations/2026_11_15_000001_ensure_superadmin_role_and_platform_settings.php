<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure 'superadmin' role exists
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'label' => 'Super Administrator',
                'description' => 'Platformaning to\'liq egasi va cheklovsiz boshqaruvchisi',
            ]
        );

        // 2. Assign 'superadmin' role to admin@market.uz and existing admin users
        $adminUser = User::where('email', 'admin@market.uz')->first();
        if ($adminUser && !$adminUser->hasRole('superadmin')) {
            $adminUser->assignRole('superadmin');
        }

        // 3. Create platform_settings table if it doesn't exist
        if (!Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // 4. Seed default settings
        $defaults = [
            [
                'key' => 'site_name',
                'value' => 'Marketplace',
                'group' => 'general',
                'description' => 'Platforma rasmiy nomi',
            ],
            [
                'key' => 'site_tagline',
                'value' => 'O\'zbekistondagi eng zamonaviy onlayn bozor',
                'group' => 'general',
                'description' => 'Platforma shiori',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+998 71 200-00-00',
                'group' => 'contact',
                'description' => 'Yordam markazi telefoni',
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@market.uz',
                'group' => 'contact',
                'description' => 'Aloqa emaili',
            ],
            [
                'key' => 'office_address',
                'value' => 'Toshkent sh., Chilonzor tumani, Bunyodkor ko\'chasi, 42-uy',
                'group' => 'contact',
                'description' => 'Ofis manzili',
            ],
            [
                'key' => 'default_commission_rate',
                'value' => '5.0',
                'group' => 'finance',
                'description' => 'Standart platforma komissiyasi (%)',
            ],
            [
                'key' => 'standard_delivery_fee',
                'value' => '25000',
                'group' => 'shipping',
                'description' => 'Standart yetkazib berish narxi (UZS)',
            ],
            [
                'key' => 'free_delivery_threshold',
                'value' => '300000',
                'group' => 'shipping',
                'description' => 'Bepul yetkazib berish chegarasi (UZS)',
            ],
            [
                'key' => 'currency',
                'value' => 'UZS',
                'group' => 'finance',
                'description' => 'Asosiy valyuta',
            ],
            [
                'key' => 'registration_enabled',
                'value' => '1',
                'group' => 'system',
                'description' => 'Yangi foydalanuvchilar ro\'yxatdan o\'tishi (1=ochiq, 0=yopiq)',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'system',
                'description' => 'Texnik tanaffus rejimi (1=yoqilgan, 0=o\'chirilgan)',
            ],
        ];

        foreach ($defaults as $setting) {
            DB::table('platform_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
