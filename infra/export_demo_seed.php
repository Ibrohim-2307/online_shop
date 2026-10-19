<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=online_shop', 'root', '');
$tables = ['roles', 'permissions', 'role_permissions', 'users', 'user_roles', 'sellers', 'seller_members', 'categories', 'products', 'product_variants', 'price_tiers', 'warehouses', 'inventory_balances', 'inventory_movements'];

$out = "-- ---------------------------------------------------------\n"
     . "-- Demo Seed Data Export for Marketplace\n"
     . "-- Conforms to TECHNICAL_SPEC.md\n"
     . "-- Generated on: " . date('Y-m-d H:i:s') . "\n"
     . "-- ---------------------------------------------------------\n\n"
     . "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $tbl) {
    $stmt = $pdo->query("SELECT * FROM `{$tbl}`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        continue;
    }

    $out .= "--\n-- Dumping data for table `{$tbl}`\n--\n";
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
echo "Exported demo_seed.sql successfully. Size: " . strlen($out) . " bytes\n";
