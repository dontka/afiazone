#!/usr/bin/env php
<?php

/**
 * Roles & Permissions Seeder
 *
 * Seeds the database with roles, permissions, and role-permission mappings.
 * Safe to run multiple times (uses INSERT IGNORE / ON DUPLICATE KEY).
 *
 * Usage: php bin/seed-roles.php [--with-admin email password]
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/helpers.php';

// Load environment
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

echo "🔐 AfiaZone — Roles & Permissions Seeder\n";
echo "==========================================\n\n";

$pdo = db();
if (!$pdo) {
    echo "❌ Database connection failed.\n";
    exit(1);
}

// ── Roles ──────────────────────────────────────────

$roles = [
    ['admin', 'Administrator with full access'],
    ['moderator', 'Moderator for content management'],
    ['merchant', 'Merchant selling on marketplace'],
    ['customer', 'Regular customer'],
    ['deliverer', 'Delivery personnel'],
    ['partner', 'Partner organization'],
];

echo "Seeding roles...\n";
$stmt = $pdo->prepare('INSERT INTO roles (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=name');
foreach ($roles as [$name, $desc]) {
    $stmt->execute([$name, $desc]);
    echo "  ✓ {$name}\n";
}

// ── Permissions ────────────────────────────────────

$permissions = [
    ['manage_users', 'Create, read, update, delete users'],
    ['manage_roles', 'Manage roles and permissions'],
    ['manage_products', 'CRUD products'],
    ['create_product', 'Create a product'],
    ['update_product', 'Update a product'],
    ['delete_product', 'Delete a product'],
    ['view_products', 'View product catalog'],
    ['manage_orders', 'Manage all orders'],
    ['create_order', 'Place an order'],
    ['update_order', 'Update order status'],
    ['view_orders', 'View orders'],
    ['cancel_order', 'Cancel an order'],
    ['manage_wallet', 'Manage wallet system'],
    ['view_wallet', 'View own wallet'],
    ['topup_wallet', 'Top up wallet'],
    ['transfer_funds', 'Transfer wallet funds'],
    ['manage_kyc', 'Review KYC submissions'],
    ['submit_kyc', 'Submit KYC documents'],
    ['manage_merchants', 'Manage merchant accounts'],
    ['manage_deliveries', 'Manage delivery assignments'],
    ['view_analytics', 'View platform analytics'],
    ['manage_prescriptions', 'Verify prescriptions'],
    ['manage_reports', 'Handle reports and flags'],
    ['manage_support', 'Handle support tickets'],
    ['manage_promotions', 'Manage promotion codes'],
    ['manage_blog', 'Create, edit, delete blog posts'],
    ['moderate_comments', 'Moderate blog comments'],
    ['manage_ads', 'Manage advertising campaigns'],
    ['manage_api_clients', 'Manage third-party API clients'],
    ['manage_translations', 'Manage translations and languages'],
    ['manage_languages', 'Add/remove supported languages'],
];

echo "\nSeeding permissions...\n";
$stmt = $pdo->prepare('INSERT INTO permissions (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=name');
foreach ($permissions as [$name, $desc]) {
    $stmt->execute([$name, $desc]);
    echo "  ✓ {$name}\n";
}

// ── Role-Permission Mappings ───────────────────────

$rolePermissions = [
    // Admin gets everything
    'admin' => '*',
    // Customer
    'customer' => [
        'view_products', 'create_order', 'view_orders', 'cancel_order',
        'view_wallet', 'topup_wallet', 'transfer_funds', 'submit_kyc',
    ],
    // Merchant
    'merchant' => [
        'view_products', 'create_product', 'update_product', 'delete_product',
        'view_orders', 'update_order', 'view_wallet', 'topup_wallet',
        'transfer_funds', 'submit_kyc', 'manage_ads',
    ],
    // Moderator
    'moderator' => [
        'manage_users', 'view_products', 'manage_orders', 'manage_kyc',
        'manage_prescriptions', 'manage_reports', 'manage_support',
        'view_analytics', 'manage_blog', 'moderate_comments',
    ],
    // Deliverer
    'deliverer' => [
        'view_orders', 'view_wallet', 'topup_wallet', 'transfer_funds', 'submit_kyc',
    ],
];

echo "\nSeeding role-permission mappings...\n";
foreach ($rolePermissions as $roleName => $perms) {
    if ($perms === '*') {
        // Admin: all permissions
        $pdo->exec(
            "INSERT INTO role_permissions (role_id, permission_id)
             SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = '{$roleName}'
             ON DUPLICATE KEY UPDATE role_id=role_id"
        );
        echo "  ✓ {$roleName} → ALL permissions\n";
    } else {
        $inList = implode(',', array_map(fn($p) => $pdo->quote($p), $perms));
        $pdo->exec(
            "INSERT INTO role_permissions (role_id, permission_id)
             SELECT r.id, p.id FROM roles r, permissions p
             WHERE r.name = '{$roleName}' AND p.name IN ({$inList})
             ON DUPLICATE KEY UPDATE role_id=role_id"
        );
        echo "  ✓ {$roleName} → " . count($perms) . " permissions\n";
    }
}

// ── Optional: Create admin user ────────────────────

$createAdmin = false;
$adminEmail = '';
$adminPassword = '';

for ($i = 1; $i < count($argv); $i++) {
    if ($argv[$i] === '--with-admin' && isset($argv[$i + 1], $argv[$i + 2])) {
        $createAdmin = true;
        $adminEmail = $argv[$i + 1];
        $adminPassword = $argv[$i + 2];
        break;
    }
}

if ($createAdmin) {
    echo "\nCreating admin user...\n";

    // Check if email exists
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$adminEmail]);
    $existing = $check->fetch();

    if ($existing) {
        $userId = $existing['id'];
        echo "  ℹ User {$adminEmail} already exists (ID: {$userId})\n";
    } else {
        $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (email, password_hash, first_name, last_name, status, email_verified_at)
             VALUES (?, ?, 'Admin', 'AfiaZone', 'active', NOW())"
        );
        $stmt->execute([$adminEmail, $hash]);
        $userId = (int) $pdo->lastInsertId();
        echo "  ✓ Created user {$adminEmail} (ID: {$userId})\n";
    }

    // Assign admin role
    $pdo->exec(
        "INSERT IGNORE INTO user_roles (user_id, role_id)
         SELECT {$userId}, id FROM roles WHERE name = 'admin'"
    );
    echo "  ✓ Assigned 'admin' role\n";

    // Create profile
    $pdo->exec(
        "INSERT IGNORE INTO user_profiles (user_id) VALUES ({$userId})"
    );
    echo "  ✓ Profile created\n";
}

// ── Summary ────────────────────────────────────────

$roleCount = $pdo->query('SELECT COUNT(*) FROM roles')->fetchColumn();
$permCount = $pdo->query('SELECT COUNT(*) FROM permissions')->fetchColumn();
$mappingCount = $pdo->query('SELECT COUNT(*) FROM role_permissions')->fetchColumn();

echo "\n✅ Seeding completed!\n";
echo "   Roles:       {$roleCount}\n";
echo "   Permissions:  {$permCount}\n";
echo "   Mappings:     {$mappingCount}\n";

if (!$createAdmin) {
    echo "\nTip: Add --with-admin email password to create an admin user:\n";
    echo "  php bin/seed-roles.php --with-admin admin@afiazone.com MySecureP@ss\n";
}
