#!/usr/bin/env php
<?php

/**
 * AfiaZone — Database Seeder Runner
 *
 * Populates the database with sample/development data.
 *
 * Usage:
 *   php bin/seed.php                   Run all seeders
 *   php bin/seed.php users             Run a specific seeder
 *   php bin/seed.php users,products    Run multiple seeders
 *   php bin/seed.php --fresh           Fresh migrate + seed all
 *   php bin/seed.php --list            List available seeders
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/helpers.php';

if (file_exists(BASE_PATH . '/.env')) {
    \Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
}

// ── Seeder registry (order matters for FK dependencies) ──
$registry = [
    'roles'         => '000_RolesPermissionsSeeder.php',
    'users'         => '001_UsersSeeder.php',
    'merchants'     => '002_MerchantsSeeder.php',
    'products'      => '003_ProductsSeeder.php',
    'orders'        => '004_OrdersSeeder.php',
    'wallet'        => '005_WalletSeeder.php',
    'delivery'      => '006_DeliverySeeder.php',
    'reviews'       => '007_ReviewsSeeder.php',
    'blog'          => '008_BlogSeeder.php',
    'notifications' => '009_NotificationsSeeder.php',
];

$seedersDir = BASE_PATH . '/database/seeders';

// ── Parse arguments ──
$args = array_slice($argv, 1);
$fresh = false;
$listMode = false;
$requested = [];

foreach ($args as $arg) {
    if ($arg === '--fresh') {
        $fresh = true;
    } elseif ($arg === '--list') {
        $listMode = true;
    } elseif (!str_starts_with($arg, '-')) {
        $requested = array_merge($requested, explode(',', $arg));
    }
}

// ── List mode ──
if ($listMode) {
    echo "📋 Available Seeders\n";
    echo str_repeat('─', 50) . "\n";
    foreach ($registry as $key => $file) {
        $exists = file_exists($seedersDir . '/' . $file) ? '✓' : '✗';
        printf("  %s %-20s %s\n", $exists, $key, $file);
    }
    exit(0);
}

echo "🌱 AfiaZone — Database Seeder\n";
echo "==============================\n\n";

// ── Fresh mode ──
if ($fresh) {
    echo "🔄 Running fresh migration first...\n\n";
    $exitCode = 0;
    passthru('php ' . escapeshellarg(BASE_PATH . '/bin/migrate.php') . ' fresh', $exitCode);
    if ($exitCode !== 0) {
        echo "\n❌ Migration failed. Aborting seeder.\n";
        exit(1);
    }
    echo "\n";
}

// ── Database connection ──
$pdo = db();
if (!$pdo) {
    // Fallback direct connection
    $dbHost = env('DB_HOST', 'localhost');
    $dbPort = (int) env('DB_PORT', 3306);
    $dbName = env('DB_NAME', 'afiazone');
    $dbUser = env('DB_USER', 'root');
    $dbPassword = env('DB_PASSWORD', '');

    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        echo "❌ Database connection failed: {$e->getMessage()}\n";
        exit(1);
    }
}

// ── Determine which seeders to run ──
$toRun = [];
if (empty($requested)) {
    $toRun = $registry;
} else {
    foreach ($requested as $key) {
        $key = trim($key);
        if (isset($registry[$key])) {
            $toRun[$key] = $registry[$key];
        } else {
            echo "⚠ Unknown seeder: {$key}\n";
        }
    }
}

if (empty($toRun)) {
    echo "Nothing to seed.\n";
    exit(0);
}

// ── Run seeders ──
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$ran = 0;
$failed = 0;

foreach ($toRun as $key => $file) {
    $path = $seedersDir . '/' . $file;
    if (!file_exists($path)) {
        echo "  ⚠ {$key}: file not found ({$file})\n";
        continue;
    }

    echo "  ▸ Seeding: {$key}...";
    try {
        $seeder = require $path;
        if (is_array($seeder) && isset($seeder['run'])) {
            ($seeder['run'])($pdo);
        }
        echo " ✓\n";
        $ran++;
    } catch (\Throwable $e) {
        echo " ✗\n";
        echo "    Error: {$e->getMessage()}\n";
        $failed++;
    }
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "\n";
if ($failed > 0) {
    echo "⚠ Completed: {$ran} seeded, {$failed} failed.\n";
    exit(1);
} else {
    echo "✅ All {$ran} seeder(s) completed.\n";
}
