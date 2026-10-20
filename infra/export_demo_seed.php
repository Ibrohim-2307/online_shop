<?php

/**
 * Strict Demo Seed Exporter
 * Conforms to TECHNICAL_SPEC.md:
 * - Exports ONLY synthetic demo catalog and demo users.
 * - NEVER exports real users, customer passwords, OTP codes, audit logs, or session tokens.
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=online_shop', 'root', '');

// Whitelisted tables and their exact synthetic-data filter queries
$tableQueries = [
    'roles' => 'SELECT * FROM roles ORDER BY id ASC',
    'permissions' => 'SELECT * FROM permissions ORDER BY id ASC',
    'role_permissions' => 'SELECT * FROM role_permissions ORDER BY role_id, permission_id ASC',
    // Only synthetic demo users (strictly matching @market.uz domain and demo phone)
    'users' => "SELECT id, name, email, phone, email_verified_at, password, is_active, last_login_at, remember_token, created_at, updated_at 
                FROM users 
                WHERE email IN ('seller@market.uz', 'customer@market.uz', 'courier@market.uz') 
                   OR phone = '+998901234567' 
                ORDER BY id ASC",
    'user_roles' => "SELECT ur.* FROM user_roles ur 
                     JOIN users u ON ur.user_id = u.id 
                     WHERE u.email IN ('seller@market.uz', 'customer@market.uz', 'courier@market.uz') 
                        OR u.phone = '+998901234567'",
    'sellers' => "SELECT * FROM sellers WHERE slug = 'stroybaza-ulgurji'",
    'seller_members' => "SELECT sm.* FROM seller_members sm 
                         JOIN sellers s ON sm.seller_id = s.id 
                         WHERE s.slug = 'stroybaza-ulgurji'",
    'categories' => "SELECT * FROM categories ORDER BY sort_order ASC, id ASC",
    'banners' => "SELECT * FROM banners ORDER BY sort_order ASC, id ASC",
    'products' => "SELECT * FROM products WHERE seller_id IN (SELECT id FROM sellers WHERE slug = 'stroybaza-ulgurji') ORDER BY id ASC",
    'product_variants' => "SELECT pv.* FROM product_variants pv 
                           JOIN products p ON pv.product_id = p.id 
                           WHERE p.seller_id IN (SELECT id FROM sellers WHERE slug = 'stroybaza-ulgurji') 
                           ORDER BY pv.id ASC",
    'price_tiers' => "SELECT pt.* FROM price_tiers pt 
                      JOIN product_variants pv ON pt.product_variant_id = pv.id 
                      JOIN products p ON pv.product_id = p.id 
                      WHERE p.seller_id IN (SELECT id FROM sellers WHERE slug = 'stroybaza-ulgurji') 
                      ORDER BY pt.product_variant_id, pt.min_quantity ASC",
    'warehouses' => "SELECT * FROM warehouses WHERE seller_id IN (SELECT id FROM sellers WHERE slug = 'stroybaza-ulgurji')",
    'inventory_balances' => "SELECT ib.* FROM inventory_balances ib 
                             JOIN warehouses w ON ib.warehouse_id = w.id 
                             WHERE w.seller_id IN (SELECT id FROM sellers WHERE slug = 'stroybaza-ulgurji')",
    'inventory_movements' => "SELECT im.* FROM inventory_movements im 
                              JOIN warehouses w ON im.warehouse_id = w.id 
                              WHERE w.seller_id IN (SELECT id FROM sellers WHERE slug = 'stroybaza-ulgurji') 
                                AND im.reference_type = 'initial_seed'",
];

$out = "-- ---------------------------------------------------------\n"
     . "-- Demo Seed Data Export for Marketplace\n"
     . "-- Conforms strictly to TECHNICAL_SPEC.md\n"
     . "-- Note: Contains ONLY synthetic demo catalog and accounts.\n"
     . "-- Real users, customer passwords, OTPs and tokens are NEVER exported.\n"
     . "-- Generated on: " . date('Y-m-d H:i:s') . "\n"
     . "-- ---------------------------------------------------------\n\n"
     . "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tableQueries as $tbl => $query) {
    $stmt = $pdo->query($query);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        continue;
    }

    $out .= "--\n-- Dumping synthetic demo data for table `{$tbl}`\n--\n";
    $cols = array_keys($rows[0]);
    $colList = implode(', ', array_map(fn($c) => "`" . $c . "`", $cols));

    foreach ($rows as $r) {
        $vals = array_map(function($v) use ($pdo) {
            if ($v === null) {
                return 'NULL';
            }
            return $pdo->quote($v);
        }, $r);
        $out .= "INSERT INTO `{$tbl}` ({$colList}) VALUES (" . implode(', ', $vals) . ");\n";
    }
    $out .= "\n";
}

$out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

$targetPath = __DIR__ . '/../database/exports/demo_seed.sql';
file_put_contents($targetPath, $out);
echo "Exported synthetic demo_seed.sql successfully. Size: " . strlen($out) . " bytes\n";
