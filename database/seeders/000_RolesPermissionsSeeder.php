<?php

declare(strict_types=1);

/**
 * Seeder: Roles, Permissions & Mappings
 * (Reference data — safe to run multiple times)
 */

return [
    'run' => function (\PDO $pdo): void {
        // Roles
        $pdo->exec("
            INSERT INTO roles (name, description) VALUES
            ('admin', 'Administrator with full access'),
            ('moderator', 'Moderator for content management'),
            ('merchant', 'Merchant selling on marketplace'),
            ('customer', 'Regular customer'),
            ('deliverer', 'Delivery personnel'),
            ('partner', 'Partner organization')
            ON DUPLICATE KEY UPDATE name=name
        ");

        // Permissions
        $pdo->exec("
            INSERT INTO permissions (name, description) VALUES
            ('manage_users', 'Create, read, update, delete users'),
            ('manage_roles', 'Manage roles and permissions'),
            ('manage_products', 'CRUD products'),
            ('create_product', 'Create a product'),
            ('update_product', 'Update a product'),
            ('delete_product', 'Delete a product'),
            ('view_products', 'View product catalog'),
            ('manage_orders', 'Manage all orders'),
            ('create_order', 'Place an order'),
            ('update_order', 'Update order status'),
            ('view_orders', 'View orders'),
            ('cancel_order', 'Cancel an order'),
            ('manage_wallet', 'Manage wallet system'),
            ('view_wallet', 'View own wallet'),
            ('topup_wallet', 'Top up wallet'),
            ('transfer_funds', 'Transfer wallet funds'),
            ('manage_kyc', 'Review KYC submissions'),
            ('submit_kyc', 'Submit KYC documents'),
            ('manage_merchants', 'Manage merchant accounts'),
            ('manage_deliveries', 'Manage delivery assignments'),
            ('view_analytics', 'View platform analytics'),
            ('manage_prescriptions', 'Verify prescriptions'),
            ('manage_reports', 'Handle reports and flags'),
            ('manage_support', 'Handle support tickets'),
            ('manage_promotions', 'Manage promotion codes'),
            ('manage_blog', 'Create, edit, delete blog posts'),
            ('moderate_comments', 'Moderate blog comments'),
            ('manage_ads', 'Manage advertising campaigns'),
            ('manage_api_clients', 'Manage third-party API clients'),
            ('manage_translations', 'Manage translations and languages'),
            ('manage_languages', 'Add/remove supported languages')
            ON DUPLICATE KEY UPDATE name=name
        ");

        // Admin → all
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin'
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Customer
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'customer' AND p.name IN ('view_products','create_order','view_orders','cancel_order','view_wallet','topup_wallet','transfer_funds','submit_kyc')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Merchant
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'merchant' AND p.name IN ('view_products','create_product','update_product','delete_product','view_orders','update_order','view_wallet','topup_wallet','transfer_funds','submit_kyc','manage_ads')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Moderator
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'moderator' AND p.name IN ('manage_users','view_products','manage_orders','manage_kyc','manage_prescriptions','manage_reports','manage_support','view_analytics','manage_blog','moderate_comments')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Deliverer
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'deliverer' AND p.name IN ('view_orders','view_wallet','topup_wallet','transfer_funds','submit_kyc')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");
    },
];
