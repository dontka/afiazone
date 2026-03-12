<?php

declare(strict_types=1);

/**
 * Migration: Languages & Translations
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS languages (
              id INT AUTO_INCREMENT PRIMARY KEY,
              code VARCHAR(10) UNIQUE NOT NULL,
              name VARCHAR(100) NOT NULL,
              native_name VARCHAR(100) NOT NULL,
              flag_icon VARCHAR(50),
              is_default BOOLEAN DEFAULT FALSE,
              is_active BOOLEAN DEFAULT TRUE,
              is_rtl BOOLEAN DEFAULT FALSE,
              display_order INT DEFAULT 0,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              INDEX idx_code (code),
              INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS translations (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              locale VARCHAR(10) NOT NULL,
              namespace VARCHAR(100) NOT NULL DEFAULT 'general',
              group_key VARCHAR(100) NOT NULL,
              item_key VARCHAR(255) NOT NULL,
              value TEXT NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY unique_translation (locale, namespace, group_key, item_key),
              INDEX idx_locale (locale),
              INDEX idx_namespace_group (namespace, group_key)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS translations");
        $pdo->exec("DROP TABLE IF EXISTS languages");
    },
];
