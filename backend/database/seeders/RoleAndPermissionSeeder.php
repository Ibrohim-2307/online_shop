<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Customer / General
            ['name' => 'view_catalog', 'label' => 'Katalogni ko\'rish', 'module' => 'catalog'],
            ['name' => 'place_order', 'label' => 'Buyurtma berish', 'module' => 'orders'],
            ['name' => 'manage_own_addresses', 'label' => 'O\'z manzillarini boshqarish', 'module' => 'identity'],
            ['name' => 'create_rfq', 'label' => 'Ulgurji narx so\'rovi yuborish (RFQ)', 'module' => 'rfq'],

            // Seller
            ['name' => 'manage_seller_profile', 'label' => 'Do\'kon profilini boshqarish', 'module' => 'sellers'],
            ['name' => 'manage_seller_members', 'label' => 'Do\'kon xodimlarini boshqarish', 'module' => 'sellers'],
            ['name' => 'manage_products', 'label' => 'Mahsulotlarni boshqarish', 'module' => 'catalog'],
            ['name' => 'manage_inventory', 'label' => 'Ombor qoldiqlarini boshqarish', 'module' => 'inventory'],
            ['name' => 'manage_seller_orders', 'label' => 'Do\'kon buyurtmalarini boshqarish', 'module' => 'orders'],
            ['name' => 'view_seller_finances', 'label' => 'Do\'kon moliyasini ko\'rish', 'module' => 'finance'],

            // Courier / Logistics
            ['name' => 'view_assigned_shipments', 'label' => 'Biriktirilgan jo\'natmalarni ko\'rish', 'module' => 'shipping'],
            ['name' => 'update_shipment_status', 'label' => 'Yetkazish holatini o\'zgartirish', 'module' => 'shipping'],
            ['name' => 'send_gps_location', 'label' => 'GPS koordinatalarini uzatish', 'module' => 'tracking'],
            ['name' => 'assign_shipments', 'label' => 'Kuryerlarga jo\'natmalarni biriktirish', 'module' => 'logistics'],

            // Moderator
            ['name' => 'moderate_sellers', 'label' => 'Sotuvchi arizalarini ko\'rib chiqish', 'module' => 'moderation'],
            ['name' => 'moderate_products', 'label' => 'Mahsulotlarni moderatsiya qilish', 'module' => 'moderation'],
            ['name' => 'moderate_reviews', 'label' => 'Sharhlarni moderatsiya qilish', 'module' => 'moderation'],

            // Admin
            ['name' => 'manage_system_settings', 'label' => 'Tizim sozlamalarini boshqarish', 'module' => 'admin'],
            ['name' => 'manage_users', 'label' => 'Foydalanuvchilarni boshqarish', 'module' => 'admin'],
            ['name' => 'manage_all_sellers', 'label' => 'Barcha sotuvchilarni boshqarish', 'module' => 'admin'],
            ['name' => 'view_audit_logs', 'label' => 'Audit loglarini ko\'rish', 'module' => 'admin'],
            ['name' => 'process_payouts', 'label' => 'To\'lovlar va hisob-kitoblarni boshqarish', 'module' => 'finance'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $roles = [
            [
                'name' => 'customer',
                'label' => 'Chakana xaridor',
                'description' => 'Oddiy ro\'yxatdan o\'tgan xaridor',
                'permissions' => ['view_catalog', 'place_order', 'manage_own_addresses'],
            ],
            [
                'name' => 'wholesale_customer',
                'label' => 'Ulgurji xaridor',
                'description' => 'Tadbirkor yoki yuridik shaxs bo\'lgan ulgurji xaridor',
                'permissions' => ['view_catalog', 'place_order', 'manage_own_addresses', 'create_rfq'],
            ],
            [
                'name' => 'seller_owner',
                'label' => 'Do\'kon egasi',
                'description' => 'Do\'kon to\'liq boshqaruvchisi va egasi',
                'permissions' => [
                    'view_catalog', 'manage_seller_profile', 'manage_seller_members',
                    'manage_products', 'manage_inventory', 'manage_seller_orders', 'view_seller_finances',
                ],
            ],
            [
                'name' => 'seller_manager',
                'label' => 'Do\'kon menejeri',
                'description' => 'Do\'kon tovarlari va buyurtmalarini boshqaruvchi',
                'permissions' => [
                    'view_catalog', 'manage_products', 'manage_inventory', 'manage_seller_orders',
                ],
            ],
            [
                'name' => 'seller_operator',
                'label' => 'Do\'kon operatori',
                'description' => 'Buyurtmalarni yig\'uvchi va qayta ishlovchi',
                'permissions' => ['manage_seller_orders'],
            ],
            [
                'name' => 'courier',
                'label' => 'Kuryer',
                'description' => 'Buyurtmalarni xaridorga yetkazib beruvchi',
                'permissions' => ['view_assigned_shipments', 'update_shipment_status', 'send_gps_location'],
            ],
            [
                'name' => 'logistics_operator',
                'label' => 'Logistika operatori',
                'description' => 'Yetkazib berish va marshrutlarni boshqaruvchi',
                'permissions' => ['view_assigned_shipments', 'assign_shipments'],
            ],
            [
                'name' => 'moderator',
                'label' => 'Moderator',
                'description' => 'Tovar, sharh va sotuvchilarni tekshiruvchi xodim',
                'permissions' => ['moderate_sellers', 'moderate_products', 'moderate_reviews'],
            ],
            [
                'name' => 'admin',
                'label' => 'Tizim administratori',
                'description' => 'Platformaning barcha modullari bo\'yicha to\'liq vakolatli rahbar',
                'permissions' => array_column($permissions, 'name'),
            ],
        ];

        foreach ($roles as $r) {
            $rolePermissions = $r['permissions'];
            unset($r['permissions']);

            $role = Role::firstOrCreate(['name' => $r['name']], $r);
            $permissionIds = Permission::whereIn('name', $rolePermissions)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
