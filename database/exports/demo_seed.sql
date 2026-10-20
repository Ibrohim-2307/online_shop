-- ---------------------------------------------------------
-- Demo Seed Data Export for Marketplace
-- Conforms strictly to TECHNICAL_SPEC.md
-- Note: Contains ONLY synthetic demo catalog and accounts.
-- Real users, customer passwords, OTPs and tokens are NEVER exported.
-- Generated on: 2026-10-20 07:15:45
-- ---------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

--
-- Dumping synthetic demo data for table `roles`
--
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('1', 'customer', 'Chakana xaridor', 'Oddiy ro\'yxatdan o\'tgan xaridor', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('2', 'wholesale_customer', 'Ulgurji xaridor', 'Tadbirkor yoki yuridik shaxs bo\'lgan ulgurji xaridor', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('3', 'seller_owner', 'Do\'kon egasi', 'Do\'kon to\'liq boshqaruvchisi va egasi', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('4', 'seller_manager', 'Do\'kon menejeri', 'Do\'kon tovarlari va buyurtmalarini boshqaruvchi', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('5', 'seller_operator', 'Do\'kon operatori', 'Buyurtmalarni yig\'uvchi va qayta ishlovchi', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('6', 'courier', 'Kuryer', 'Buyurtmalarni xaridorga yetkazib beruvchi', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('7', 'logistics_operator', 'Logistika operatori', 'Yetkazib berish va marshrutlarni boshqaruvchi', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('8', 'moderator', 'Moderator', 'Tovar, sharh va sotuvchilarni tekshiruvchi xodim', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `roles` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES ('9', 'admin', 'Tizim administratori', 'Platformaning barcha modullari bo\'yicha to\'liq vakolatli rahbar', '2026-10-19 15:49:46', '2026-10-19 15:49:46');

--
-- Dumping synthetic demo data for table `permissions`
--
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('1', 'view_catalog', 'Katalogni ko\'rish', 'catalog', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('2', 'place_order', 'Buyurtma berish', 'orders', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('3', 'manage_own_addresses', 'O\'z manzillarini boshqarish', 'identity', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('4', 'create_rfq', 'Ulgurji narx so\'rovi yuborish (RFQ)', 'rfq', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('5', 'manage_seller_profile', 'Do\'kon profilini boshqarish', 'sellers', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('6', 'manage_seller_members', 'Do\'kon xodimlarini boshqarish', 'sellers', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('7', 'manage_products', 'Mahsulotlarni boshqarish', 'catalog', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('8', 'manage_inventory', 'Ombor qoldiqlarini boshqarish', 'inventory', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('9', 'manage_seller_orders', 'Do\'kon buyurtmalarini boshqarish', 'orders', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('10', 'view_seller_finances', 'Do\'kon moliyasini ko\'rish', 'finance', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('11', 'view_assigned_shipments', 'Biriktirilgan jo\'natmalarni ko\'rish', 'shipping', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('12', 'update_shipment_status', 'Yetkazish holatini o\'zgartirish', 'shipping', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('13', 'send_gps_location', 'GPS koordinatalarini uzatish', 'tracking', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('14', 'assign_shipments', 'Kuryerlarga jo\'natmalarni biriktirish', 'logistics', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('15', 'moderate_sellers', 'Sotuvchi arizalarini ko\'rib chiqish', 'moderation', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('16', 'moderate_products', 'Mahsulotlarni moderatsiya qilish', 'moderation', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('17', 'moderate_reviews', 'Sharhlarni moderatsiya qilish', 'moderation', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('18', 'manage_system_settings', 'Tizim sozlamalarini boshqarish', 'admin', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('19', 'manage_users', 'Foydalanuvchilarni boshqarish', 'admin', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('20', 'manage_all_sellers', 'Barcha sotuvchilarni boshqarish', 'admin', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('21', 'view_audit_logs', 'Audit loglarini ko\'rish', 'admin', '2026-10-19 15:49:46', '2026-10-19 15:49:46');
INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES ('22', 'process_payouts', 'To\'lovlar va hisob-kitoblarni boshqarish', 'finance', '2026-10-19 15:49:46', '2026-10-19 15:49:46');

--
-- Dumping synthetic demo data for table `role_permissions`
--
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '2');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '3');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '2');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '3');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '4');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '5');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '6');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '10');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('6', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('6', '12');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('6', '13');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('7', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('7', '14');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('8', '15');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('8', '16');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('8', '17');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '2');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '3');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '4');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '5');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '6');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '10');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '12');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '13');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '14');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '15');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '16');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '17');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '18');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '19');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '20');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '21');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('9', '22');

--
-- Dumping synthetic demo data for table `users`
--
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'Akbar Sotuvchi', 'seller@market.uz', '+998901234567', NULL, '$2y$12$GInGLEIp9/kAZBpbJ9mhf.1tpshsiUsEjowKUFsnBFm1xdqkDpFCi', '1', NULL, NULL, '2026-10-19 16:37:09', '2026-10-19 16:37:09');
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', 'Dilshod Xaridor', 'customer@market.uz', '+998909876543', NULL, '$2y$12$ncbN6X/tT1skDno0heJEFOxTamuils/vYTiSCH2btS/qD0gdFrfZm', '1', NULL, NULL, '2026-10-20 07:08:45', '2026-10-20 07:08:45');
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES ('3', 'Rustam Kuryer', 'courier@market.uz', '+998905554433', NULL, '$2y$12$p7kb9W0aoreAeYjeAVSOCe3TZKyFjjRgZ8yoaVmBBSq9nvV.8jmlG', '1', NULL, NULL, '2026-10-20 07:08:46', '2026-10-20 07:08:46');

--
-- Dumping synthetic demo data for table `user_roles`
--
INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('1', '3', '2026-10-19 16:37:09', '2026-10-19 16:37:09');
INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('2', '1', '2026-10-20 07:08:45', '2026-10-20 07:08:45');
INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('3', '6', '2026-10-20 07:08:46', '2026-10-20 07:08:46');

--
-- Dumping synthetic demo data for table `sellers`
--
INSERT INTO `sellers` (`id`, `name`, `slug`, `legal_name`, `tax_id`, `status`, `phone`, `email`, `address`, `commission_rate`, `logo_path`, `rating`, `created_at`, `updated_at`) VALUES ('1', 'StroyBaza Ulgurji Savdo', 'stroybaza-ulgurji', 'StroyBaza Grand MCHJ', '308123456', 'active', '+998901234567', 'contact@stroybaza.uz', 'Toshkent sh., Sergeli tumani, Yangi Sergeli ko\'chasi, 24-uy', '5.00', NULL, '4.90', '2026-10-19 16:37:27', '2026-10-19 16:37:27');

--
-- Dumping synthetic demo data for table `seller_members`
--
INSERT INTO `seller_members` (`id`, `seller_id`, `user_id`, `role`, `permissions`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'owner', '[\"*\"]', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');

--
-- Dumping synthetic demo data for table `categories`
--
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('1', NULL, 'Qurilish materiallari', 'qurilish-materiallari', NULL, NULL, '1', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('2', NULL, 'Sanoat uskunalari', 'sanoat-uskunalari', NULL, NULL, '2', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('3', NULL, 'Oziq-ovqat va ichimliklar', 'oziq-ovqat-va-ichimliklar', NULL, NULL, '3', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');

--
-- Dumping synthetic demo data for table `products`
--
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Qurilish Sementi M-500', 'sement-m500', 'Yuqori sifatli portland sement M-500. Poydevor va monolit quyish uchun mo\'ljallangan.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '1', '2', 'Sanoat Dizel Generatori 300 kW', 'sanoat-generator-300kw', 'Avtonom elektr ta\'minoti uchun yuqori quvvatli dizel generator stansiyasi.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', '3', 'Tog\' Chashmasi Mineral Suvi 1.5L', 'mineral-suv-15l', 'Gazlanmagan tabiiy tog\' mineral suvi, 12 dona butilka blokda.', 'both', 'box', '12', '1.000', '5.000', 'approved', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '1', '1', 'Po\'lat Armatura A500C 12mm', 'armatura-a500c', 'Issiq prokatlangan po\'lat armatura, davlat standarti GOST 52544.', 'wholesale_only', 'ton', NULL, '0.500', '1.000', 'approved', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');

--
-- Dumping synthetic demo data for table `product_variants`
--
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'CEMENT-M500-BAG', '50 kg qop', '{\"brand\": \"Bekobod Sement\", \"weight\": \"50kg\"}', '4780001234567', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '2', 'GEN-IND-300M', '300 kW Standart Komplektatsiya', '{\"fuel\": \"Diesel\", \"power\": \"300kW\"}', '4780009876543', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '3', 'WATER-15L-BOX12', '12 talik Blok', '{\"volume\": \"1.5L\", \"packaging\": \"box\"}', '4780005551122', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '4', 'ARM-A500C-12MM-TON', '12mm tonna', '{\"grade\": \"A500C\", \"diameter\": \"12mm\"}', '4780007778899', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');

--
-- Dumping synthetic demo data for table `price_tiers`
--
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('1', '1', '1.000', '299.000', '300000', 'UZS', '2026-10-19 16:37:27', '2026-10-20 07:08:45');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('2', '1', '300.000', '999.000', '270000', 'UZS', '2026-10-19 16:37:27', '2026-10-20 07:08:45');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('3', '1', '1000.000', NULL, '250000', 'UZS', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('4', '2', '1.000', '9.000', '300000000', 'UZS', '2026-10-19 16:37:53', '2026-10-20 07:08:45');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('5', '2', '10.000', NULL, '270000000', 'UZS', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('6', '3', '5.000', '49.000', '48000', 'UZS', '2026-10-19 16:37:53', '2026-10-20 07:08:45');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('7', '3', '50.000', NULL, '42000', 'UZS', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('8', '4', '1.000', '9.500', '9500000', 'UZS', '2026-10-19 16:37:53', '2026-10-20 07:08:45');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('9', '4', '10.000', NULL, '8900000', 'UZS', '2026-10-19 16:37:53', '2026-10-19 16:37:53');

--
-- Dumping synthetic demo data for table `warehouses`
--
INSERT INTO `warehouses` (`id`, `seller_id`, `name`, `code`, `address`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Asosiy Ombor (Sergeli)', 'WH-SERGELI-01', 'Toshkent sh., Sergeli sanoat zonasi, 14-bino', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');

--
-- Dumping synthetic demo data for table `inventory_balances`
--
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '50000.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:08:45');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('2', '1', '2', '50.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:08:45');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('3', '1', '3', '2000.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:08:45');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('4', '1', '4', '350.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:08:45');

--
-- Dumping synthetic demo data for table `inventory_movements`
--
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('1', '1', '1', 'inflow', '50000.000', '0.000', '50000.000', 'Boshlang\'ich ombor kirimi', 'initial_seed', NULL, '1', '2026-10-19 19:37:53');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('2', '1', '2', 'inflow', '50.000', '0.000', '50.000', 'Zavoddan qabul qilingan generatorlar', 'initial_seed', NULL, '1', '2026-10-19 19:37:53');

SET FOREIGN_KEY_CHECKS = 1;
