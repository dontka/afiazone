<?php

declare(strict_types=1);

/**
 * Migration: Promotions, Partnerships, Insurance Plans & Subscriptions
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS promotion_codes (
              id INT AUTO_INCREMENT PRIMARY KEY,
              code VARCHAR(50) UNIQUE NOT NULL,
              discount_type ENUM('percentage','fixed_amount') DEFAULT 'percentage',
              discount_value DECIMAL(10,2),
              max_uses INT,
              current_uses INT DEFAULT 0,
              usage_per_user INT DEFAULT 1,
              min_order_amount DECIMAL(12,2),
              applicable_to ENUM('all_products','specific_category','specific_merchant') DEFAULT 'all_products',
              applicable_item_id INT,
              start_date DATETIME,
              end_date DATETIME,
              is_active BOOLEAN DEFAULT TRUE,
              created_by BIGINT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (created_by) REFERENCES users(id),
              INDEX idx_code (code),
              INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS promotion_code_usages (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              promotion_code_id INT NOT NULL,
              user_id BIGINT NOT NULL,
              order_id BIGINT,
              discount_amount DECIMAL(12,2),
              used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (promotion_code_id) REFERENCES promotion_codes(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS partnerships (
              id INT AUTO_INCREMENT PRIMARY KEY,
              partner_name VARCHAR(255),
              partner_type ENUM('insurance','mutual','pharmacy','clinic','laboratory') DEFAULT 'insurance',
              contact_person VARCHAR(255),
              email VARCHAR(255),
              phone VARCHAR(20),
              is_active BOOLEAN DEFAULT TRUE,
              agreement_url VARCHAR(512),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS insurance_plans (
              id INT AUTO_INCREMENT PRIMARY KEY,
              partnership_id INT,
              plan_name VARCHAR(255),
              description TEXT,
              monthly_premium DECIMAL(10,2),
              coverage_percentage DECIMAL(5,2),
              max_coverage_amount DECIMAL(14,2),
              is_active BOOLEAN DEFAULT TRUE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (partnership_id) REFERENCES partnerships(id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS insurance_subscriptions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL,
              insurance_plan_id INT,
              status ENUM('active','cancelled','expired','suspended') DEFAULT 'active',
              start_date DATE,
              end_date DATE,
              auto_renew BOOLEAN DEFAULT TRUE,
              premium_paid_to_date DATE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plans(id),
              INDEX idx_user_id (user_id),
              INDEX idx_status (status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS insurance_subscriptions");
        $pdo->exec("DROP TABLE IF EXISTS insurance_plans");
        $pdo->exec("DROP TABLE IF EXISTS partnerships");
        $pdo->exec("DROP TABLE IF EXISTS promotion_code_usages");
        $pdo->exec("DROP TABLE IF EXISTS promotion_codes");
    },
];
