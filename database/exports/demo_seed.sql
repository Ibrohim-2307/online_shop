-- ---------------------------------------------------------
-- Demo Seed Data Export for Marketplace
-- Conforms strictly to TECHNICAL_SPEC.md
-- Note: Contains ONLY synthetic demo catalog and accounts.
-- Real users, customer passwords, OTPs and tokens are NEVER exported.
-- Generated on: 2026-10-20 07:48:43
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
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('4', NULL, 'Elektr va yoritish jihozlari', 'elektr-jihozlari', NULL, NULL, '4', '1', '2026-10-20 07:47:59', '2026-10-20 07:47:59');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('5', NULL, 'Bo\'yoqlar va kimyoviy mahsulotlar', 'boyoqlar-va-kimyo', NULL, NULL, '5', '1', '2026-10-20 07:47:59', '2026-10-20 07:47:59');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('6', NULL, 'Santexnika va quvurlar', 'santexnika-va-quvurlar', NULL, NULL, '6', '1', '2026-10-20 07:47:59', '2026-10-20 07:47:59');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('7', NULL, 'Qurilish asbob-uskunalari', 'asbob-uskunalar', NULL, NULL, '7', '1', '2026-10-20 07:47:59', '2026-10-20 07:47:59');

--
-- Dumping synthetic demo data for table `products`
--
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Qurilish Sementi M-500', 'sement-m500', 'Yuqori sifatli portland sement M-500. Poydevor va monolit quyish uchun mo\'ljallangan.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '1', '2', 'Sanoat Dizel Generatori 300 kW', 'sanoat-generator-300kw', 'Avtonom elektr ta\'minoti uchun yuqori quvvatli dizel generator stansiyasi.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', '3', 'Tog\' Chashmasi Mineral Suvi 1.5L', 'mineral-suv-15l', 'Gazlanmagan tabiiy tog\' mineral suvi, 12 dona butilka blokda.', 'both', 'box', '12', '1.000', '5.000', 'approved', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '1', '1', 'Po\'lat Armatura A500C 12mm', 'armatura-a500c', 'Issiq prokatlangan po\'lat armatura, davlat standarti GOST 52544.', 'wholesale_only', 'ton', NULL, '0.500', '1.000', 'approved', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('5', '1', '1', 'Gipsokarton Knauf 9.5mm (Namlikka chidamli)', 'gipsokarton-knauf-95mm', 'Knauf sifatli yashil namlikka chidamli gipsokarton plitasi. O\'lchami: 1200x2500x9.5 mm.', 'both', 'piece', NULL, '1.000', '5.000', 'approved', '1', '2026-10-20 07:47:59', '2026-10-20 07:47:59');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('6', '1', '1', 'Pishiq qizil g\'isht M-125 (Standart)', 'pishiq-qizil-gisht-m125', 'Devor va poydevor ko\'tarish uchun mo\'ljallangan standart pishiq qizil g\'isht M-125.', 'wholesale_only', 'piece', NULL, '500.000', '1000.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('7', '1', '1', 'Shlakoblok Devorbop 20x20x40', 'shlakoblok-devorbop-20x20x40', 'Yuqori bosimda presslangan standart shlakoblok. Mustahkam va issiqlikni yaxshi saqlaydi.', 'both', 'piece', NULL, '50.000', '50.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('8', '1', '1', 'Kafel va Plitka Yelimi Knauf Perlfix 25kg', 'kafel-yelimi-knauf-perlfix-25kg', 'Ichki va tashqi pardozlash ishlari uchun yuqori yopishqoqlikdagi kafel yelimi.', 'both', 'piece', NULL, '1.000', '2.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('9', '1', '5', 'Akril Fasad Bo\'yog\'i MasterColor 20kg', 'akril-fasad-boyogi-mastercolor-20kg', 'Ob-havo ta\'siriga chidamli oq fasad bo\'yog\'i. Suv o\'tkazmaydi, quyoshda so\'nmaydi.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('10', '1', '4', 'Mis Elektr Kabeli VVG-P 3x2.5 (100m buxta)', 'mis-elektr-kabeli-vvg-p-3x25', 'Davlat standarti (GOST) bo\'yicha toza misdan tayyorlangan kuchlanish kabeli 3x2.5 mm.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('11', '1', '4', 'LED Sanoat Projektori 200W IP66 (Sovuq oq)', 'led-sanoat-projektori-200w-ip66', 'Omborlar, qurilish maydonlari va stadionlar uchun quvvatli LED projektor. Suv va changdan to\'liq himoyalangan.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('12', '1', '1', 'Bazalt Mineral Paxta TeploIzol 50mm', 'bazalt-mineral-paxta-teploizol-50mm', 'Uy tomi, devorlari va pollarni issiqlik va shovqindan izolyatsiya qilish uchun mineral vata.', 'both', 'box', NULL, '1.000', '5.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('13', '1', '6', 'Polipropilen Issiq Suv Quvuri PN20 D25mm (4m)', 'polipropilen-quvur-pn20-d25mm-4m', 'Issiq va sovuq suv tarmoqlari hamda isitish tizimlari uchun shisha tolali mustahkamlangan quvur.', 'both', 'piece', NULL, '5.000', '10.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('14', '1', '3', 'Samarqand Bug\'doy Uni \"Oltin Don\" 1-nav 50kg', 'samarqand-bugdoy-uni-oltin-don-50kg', 'Nonvoyxonalar va qandolatchilik korxonalari uchun yuqori navli sifatli bug\'doy uni.', 'both', 'piece', NULL, '1.000', '2.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('15', '1', '3', 'Paxta Yog\'i \"Zilol\" Rafinatsiyalangan 5L (Quti = 4 dona)', 'paxta-yogi-zilol-5l-quti', 'Tozalangan va xidsizlantirilgan yuqori sifatli tabiiy paxta yog\'i.', 'both', 'box', NULL, '1.000', '2.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('16', '1', '7', 'Lazerli Nivelir 4D 16 Liniyali \"ProLaser 360\"', 'lazerli-nivelir-4d-16-liniyali-prolaser', 'Yashil nurli professional 360 daraja 16 liniyali lazer sathi. Pult va shtativ bilan birga.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('17', '1', '2', 'Payvandlash Apparati Invertor IGBT 250A \"WeldPro\"', 'payvandlash-apparati-invertor-igbt-250a', 'Elektr kuchlanishi pasayganda ham barqaror ishlaydigan professional payvandlash uskunasi.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('18', '1', '1', 'Kerapogranit Plitasi 60x60 sm (Quti = 1.44 m2)', 'keramogranit-plitasi-60x60-sm', 'Marmar to\'qimali jilolangan yuqori darajada mustahkam keramogranit pol plitasi.', 'both', 'box', NULL, '1.000', '5.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('19', '1', '1', 'Po\'lat Profil Truba 40x20x2mm (6 metr)', 'polat-profil-truba-40x20x2mm-6m', 'Darvoza, to\'siq va karkas konstruksiyalari uchun to\'g\'ri burchakli profil trubasi.', 'both', 'piece', NULL, '1.000', '5.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `trade_mode`, `unit`, `items_per_box`, `unit_step`, `min_order_quantity`, `status`, `is_active`, `created_at`, `updated_at`) VALUES ('20', '1', '2', 'Porshenli Havo Kompressori 100L \"AirPower 3HP\"', 'porshenli-havo-kompressori-100l', 'Avtoservis va ishlab chiqarish ustaxonalari uchun 8 bar bosimli 3 ot kuchiga ega havo kompressori.', 'both', 'piece', NULL, '1.000', '1.000', 'approved', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');

--
-- Dumping synthetic demo data for table `product_variants`
--
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'CEMENT-M500-BAG', '50 kg qop', '{\"brand\": \"Bekobod Sement\", \"weight\": \"50kg\"}', '4780001234567', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '2', 'GEN-IND-300M', '300 kW Standart Komplektatsiya', '{\"fuel\": \"Diesel\", \"power\": \"300kW\"}', '4780009876543', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '3', 'WATER-15L-BOX12', '12 talik Blok', '{\"volume\": \"1.5L\", \"packaging\": \"box\"}', '4780005551122', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '4', 'ARM-A500C-12MM-TON', '12mm tonna', '{\"grade\": \"A500C\", \"diameter\": \"12mm\"}', '4780007778899', '1', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('5', '5', 'KNAUF-GKB-95-2500', '9.5mm 1.2x2.5m Yashil', '{\"size\": \"1.2x2.5m\", \"type\": \"Moisture resistant\", \"thickness\": \"9.5mm\"}', '4780009112233', '1', '2026-10-20 07:47:59', '2026-10-20 07:47:59');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('6', '6', 'BRICK-RED-M125', 'M-125 Qizil donali', '{\"brand\": \"Yangiyo\'l G\'isht Zavodi\", \"grade\": \"M-125\"}', '4780001002003', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('7', '7', 'SHLAK-20-20-40', '20x20x40 sm standart', '{\"weight\": \"16kg\", \"dimensions\": \"20x20x40cm\"}', '4780003004005', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('8', '8', 'KNAUF-PERLFIX-25KG', '25 kg qop', '{\"brand\": \"Knauf\", \"weight\": \"25kg\"}', '4780005006007', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('9', '9', 'PAINT-MC-FACADE-20KG', '20 kg plastik chelak', '{\"base\": \"Acrylic\", \"color\": \"White\", \"volume\": \"20kg\"}', '4780007008009', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('10', '10', 'CABLE-VVGP-3X25-100M', '100 metr buxta', '{\"core\": \"3x2.5\", \"length\": \"100m\", \"conductor\": \"Copper\"}', '4780009001011', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('11', '11', 'LED-FLOOD-200W-IP66', '200W IP66 6500K', '{\"light\": \"6500K\", \"power\": \"200W\", \"protection\": \"IP66\"}', '4780009002022', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('12', '12', 'BASALT-WOOL-50MM-PACK', '50mm 6 kv.m o\'ram', '{\"density\": \"35kg/m3\", \"coverage\": \"6m2\", \"thickness\": \"50mm\"}', '4780009003033', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('13', '13', 'PIPE-PPR-PN20-D25-4M', 'D25mm 4 metr', '{\"length\": \"4m\", \"diameter\": \"25mm\", \"pressure\": \"PN20\"}', '4780009004044', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('14', '14', 'FLOUR-OLTIN-DON-50KG', '50 kg qop 1-nav', '{\"grade\": \"1st Grade\", \"origin\": \"Samarqand\", \"weight\": \"50kg\"}', '4780009005055', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('15', '15', 'OIL-ZILOL-5L-BOX4', 'Qutida 4x5L (20L jami)', '{\"bottle\": \"5L\", \"packaging\": \"4 bottles / box\"}', '4780009006066', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('16', '16', 'LASER-4D-16LINE-SET', 'To\'liq komplekt + 2 ta akkumulyator', '{\"beam\": \"Green 532nm\", \"lines\": \"16\", \"accuracy\": \"±1mm/7m\"}', '4780009007077', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('17', '17', 'WELD-IGBT-250A-PRO', '250A Raqamli displey bilan', '{\"current\": \"20-250A\", \"electrode\": \"1.6-5.0mm\", \"technology\": \"IGBT\"}', '4780009008088', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('18', '18', 'GRANITE-60X60-BOX', '1.44 kv.m (4 dona plita / quti)', '{\"size\": \"60x60cm\", \"surface\": \"Polished\", \"box_area\": \"1.44m2\"}', '4780009009099', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('19', '19', 'PROFILE-PIPE-40X20-6M', '40x20 mm 6 metr donali', '{\"length\": \"6m\", \"thickness\": \"2mm\", \"dimensions\": \"40x20mm\"}', '4780009010100', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `attributes`, `barcode`, `is_active`, `created_at`, `updated_at`) VALUES ('20', '20', 'COMPRESSOR-100L-3HP', '100 Litr 8 bar 2.2kW', '{\"tank\": \"100L\", \"power\": \"3HP (2.2kW)\", \"pressure\": \"8 bar\"}', '4780009011111', '1', '2026-10-20 07:48:14', '2026-10-20 07:48:14');

--
-- Dumping synthetic demo data for table `price_tiers`
--
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('1', '1', '1.000', '299.000', '300000', 'UZS', '2026-10-19 16:37:27', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('2', '1', '300.000', '999.000', '270000', 'UZS', '2026-10-19 16:37:27', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('3', '1', '1000.000', NULL, '250000', 'UZS', '2026-10-19 16:37:27', '2026-10-19 16:37:27');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('4', '2', '1.000', '9.000', '300000000', 'UZS', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('5', '2', '10.000', NULL, '270000000', 'UZS', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('6', '3', '5.000', '49.000', '48000', 'UZS', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('7', '3', '50.000', NULL, '42000', 'UZS', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('8', '4', '1.000', '9.500', '9500000', 'UZS', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('9', '4', '10.000', NULL, '8900000', 'UZS', '2026-10-19 16:37:53', '2026-10-19 16:37:53');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('10', '5', '5.000', '49.000', '68000', 'UZS', '2026-10-20 07:47:59', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('11', '5', '50.000', '199.000', '62000', 'UZS', '2026-10-20 07:47:59', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('12', '5', '200.000', NULL, '57000', 'UZS', '2026-10-20 07:47:59', '2026-10-20 07:47:59');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('13', '6', '1000.000', '4999.000', '1800', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('14', '6', '5000.000', '19999.000', '1650', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('15', '6', '20000.000', NULL, '1500', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('16', '7', '50.000', '499.000', '5500', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('17', '7', '500.000', '1999.000', '4900', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('18', '7', '2000.000', NULL, '4400', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('19', '8', '2.000', '49.000', '48000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('20', '8', '50.000', '199.000', '42000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('21', '8', '200.000', NULL, '37000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('22', '9', '1.000', '9.000', '320000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('23', '9', '10.000', '49.000', '285000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('24', '9', '50.000', NULL, '250000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('25', '10', '1.000', '9.000', '850000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('26', '10', '10.000', '49.000', '780000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('27', '10', '50.000', NULL, '720000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('28', '11', '1.000', '9.000', '340000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('29', '11', '10.000', '49.000', '295000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('30', '11', '50.000', NULL, '260000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('31', '12', '5.000', '49.000', '185000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('32', '12', '50.000', '199.000', '165000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('33', '12', '200.000', NULL, '148000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('34', '13', '10.000', '99.000', '28000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('35', '13', '100.000', '499.000', '24000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('36', '13', '500.000', NULL, '21000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('37', '14', '2.000', '49.000', '310000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('38', '14', '50.000', '199.000', '280000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('39', '14', '200.000', NULL, '260000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('40', '15', '2.000', '19.000', '340000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('41', '15', '20.000', '99.000', '310000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('42', '15', '100.000', NULL, '285000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('43', '16', '1.000', '4.000', '1250000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('44', '16', '5.000', '19.000', '1050000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('45', '16', '20.000', NULL, '920000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('46', '17', '1.000', '4.000', '1850000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('47', '17', '5.000', '19.000', '1620000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('48', '17', '20.000', NULL, '1450000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('49', '18', '5.000', '49.000', '165000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('50', '18', '50.000', '199.000', '145000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('51', '18', '200.000', NULL, '130000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('52', '19', '5.000', '49.000', '95000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('53', '19', '50.000', '199.000', '86000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('54', '19', '200.000', NULL, '78000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('55', '20', '1.000', '3.000', '4200000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('56', '20', '4.000', '9.000', '3750000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `price_tiers` (`id`, `product_variant_id`, `min_quantity`, `max_quantity`, `unit_price`, `currency`, `created_at`, `updated_at`) VALUES ('57', '20', '10.000', NULL, '3400000', 'UZS', '2026-10-20 07:48:14', '2026-10-20 07:48:14');

--
-- Dumping synthetic demo data for table `warehouses`
--
INSERT INTO `warehouses` (`id`, `seller_id`, `name`, `code`, `address`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Asosiy Ombor (Sergeli)', 'WH-SERGELI-01', 'Toshkent sh., Sergeli sanoat zonasi, 14-bino', '1', '2026-10-19 16:37:27', '2026-10-19 16:37:27');

--
-- Dumping synthetic demo data for table `inventory_balances`
--
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '50000.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('2', '1', '2', '50.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('3', '1', '3', '2000.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('4', '1', '4', '350.000', '0.000', '2026-10-19 16:37:53', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('5', '1', '5', '1200.000', '0.000', '2026-10-20 07:47:59', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('6', '1', '6', '250000.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('7', '1', '7', '15000.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('8', '1', '8', '3500.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('9', '1', '9', '850.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('10', '1', '10', '450.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('11', '1', '11', '600.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('12', '1', '12', '1800.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('13', '1', '13', '8000.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('14', '1', '14', '4200.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('15', '1', '15', '1400.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('16', '1', '16', '180.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('17', '1', '17', '120.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('18', '1', '18', '2200.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('19', '1', '19', '5500.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');
INSERT INTO `inventory_balances` (`id`, `warehouse_id`, `product_variant_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES ('20', '1', '20', '85.000', '0.000', '2026-10-20 07:48:14', '2026-10-20 07:48:14');

--
-- Dumping synthetic demo data for table `inventory_movements`
--
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('1', '1', '1', 'inflow', '50000.000', '0.000', '50000.000', 'Boshlang\'ich ombor kirimi', 'initial_seed', NULL, '1', '2026-10-19 19:37:53');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('2', '1', '2', 'inflow', '50.000', '0.000', '50.000', 'Zavoddan qabul qilingan generatorlar', 'initial_seed', NULL, '1', '2026-10-19 19:37:53');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('3', '1', '5', 'inflow', '1200.000', '0.000', '1200.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('4', '1', '6', 'inflow', '250000.000', '0.000', '250000.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('5', '1', '7', 'inflow', '15000.000', '0.000', '15000.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('6', '1', '8', 'inflow', '3500.000', '0.000', '3500.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('7', '1', '9', 'inflow', '850.000', '0.000', '850.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('8', '1', '10', 'inflow', '450.000', '0.000', '450.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('9', '1', '11', 'inflow', '600.000', '0.000', '600.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('10', '1', '12', 'inflow', '1800.000', '0.000', '1800.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('11', '1', '13', 'inflow', '8000.000', '0.000', '8000.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('12', '1', '14', 'inflow', '4200.000', '0.000', '4200.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('13', '1', '15', 'inflow', '1400.000', '0.000', '1400.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('14', '1', '16', 'inflow', '180.000', '0.000', '180.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('15', '1', '17', 'inflow', '120.000', '0.000', '120.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('16', '1', '18', 'inflow', '2200.000', '0.000', '2200.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('17', '1', '19', 'inflow', '5500.000', '0.000', '5500.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');
INSERT INTO `inventory_movements` (`id`, `warehouse_id`, `product_variant_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_type`, `reference_id`, `user_id`, `created_at`) VALUES ('18', '1', '20', 'inflow', '85.000', '0.000', '85.000', 'Dastlabki ombor zaxirasini yuklash', 'initial_seed', '1', NULL, '2026-10-20 10:48:14');

SET FOREIGN_KEY_CHECKS = 1;
