<?php

declare(strict_types=1);

/**
 * Initial schema migration — runs the full schema.sql.
 * Running this migration creates all tables from scratch.
 */

return [
    'up' => function (\PDO $pdo): void {
        $sql = file_get_contents(BASE_PATH . '/database/schema.sql');
        $pdo->exec($sql);
    },

    'down' => function (\PDO $pdo): void {
        // Reverse order of creation — drop all tables
        $tables = [
            'promotion_code_usages', 'promotion_codes',
            'api_logs', 'audit_logs', 'analytics_events',
            'support_messages', 'support_tickets', 'reports',
            'delivery_reviews', 'merchant_reviews', 'product_reviews',
            'notifications',
            'refunds', 'payment_reconciliations', 'payment_transactions', 'user_payment_methods',
            'insurance_subscriptions', 'insurance_plans', 'partnerships',
            'consultation_messages', 'consultations',
            'medical_record_access', 'medical_records',
            'prescription_verification_logs', 'prescriptions',
            'wallet_reservations', 'wallet_topups', 'wallet_balance_history',
            'wallet_transactions', 'wallets',
            'shipment_tracking_logs', 'shipments',
            'delivery_personnel', 'delivery_providers', 'delivery_addresses',
            'order_status_logs', 'order_items', 'orders',
            'shopping_cart_items', 'shopping_carts',
            'merchant_stocks', 'merchant_fees', 'merchant_shipping_info', 'merchants', 'merchant_tiers',
            'product_attributes', 'product_variants', 'product_images', 'products', 'product_categories',
            'role_permissions', 'permissions',
            'tokens', 'user_profiles', 'user_roles', 'roles', 'users',
        ];

        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
    },
];
