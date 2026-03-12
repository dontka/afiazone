<?php

declare(strict_types=1);

/**
 * Migration: Merchant Tiers, Merchants, Shipping & Fees
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchant_tiers (
              id INT AUTO_INCREMENT PRIMARY KEY,
              name ENUM('verified','premium','gold','diamond') UNIQUE,
              display_name VARCHAR(50),
              requirements_json JSON,
              sales_commission_percent DECIMAL(5,2),
              advertisement_fee DECIMAL(10,2),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchants (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT UNIQUE NOT NULL,
              business_name VARCHAR(255),
              business_type ENUM('wholesaler','producer','retailer') DEFAULT 'retailer',
              tier_id INT DEFAULT 1,
              description TEXT,
              logo_url VARCHAR(512),
              cover_image_url VARCHAR(512),
              rating DECIMAL(3,2) DEFAULT 0,
              total_reviews INT DEFAULT 0,
              total_sales DECIMAL(14,2) DEFAULT 0,
              status ENUM('active','suspended','banned') DEFAULT 'active',
              verification_date DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (tier_id) REFERENCES merchant_tiers(id),
              INDEX idx_status (status),
              INDEX idx_tier_id (tier_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchant_shipping_info (
              merchant_id BIGINT PRIMARY KEY,
              warehouse_address VARCHAR(512),
              warehouse_city VARCHAR(100),
              warehouse_country VARCHAR(100),
              return_policy TEXT,
              processing_time_days INT,
              accepts_cash_on_delivery BOOLEAN DEFAULT TRUE,
              accepts_wallet_payment BOOLEAN DEFAULT TRUE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchant_fees (
              merchant_id BIGINT PRIMARY KEY,
              commission_percent DECIMAL(5,2),
              return_fee_percent DECIMAL(5,2),
              refund_processing_days INT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS merchant_fees");
        $pdo->exec("DROP TABLE IF EXISTS merchant_shipping_info");
        $pdo->exec("DROP TABLE IF EXISTS merchants");
        $pdo->exec("DROP TABLE IF EXISTS merchant_tiers");
    },
];
