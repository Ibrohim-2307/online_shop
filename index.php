<?php
// Auto-forward port 80 or web requests to the running Marketplace server on port 8000
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$port = (int)($_SERVER['SERVER_PORT'] ?? 80);

if ($port !== 8000) {
    $hostname = explode(':', $host)[0];
    header("Location: http://{$hostname}:8000{$uri}");
    exit;
}

require __DIR__ . '/backend/public/index.php';
