<?php

declare(strict_types=1);

/**
 * Migration: Ad Campaigns, Placements, Impressions, Clicks
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ad_campaigns (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              merchant_id BIGINT NOT NULL,
              name VARCHAR(255) NOT NULL,
              campaign_type ENUM('banner','sidebar','featured_product','popup','interstitial') DEFAULT 'banner',
              target_url VARCHAR(512),
              image_url VARCHAR(512),
              content_html TEXT,
              target_category_id INT,
              target_location VARCHAR(100),
              target_user_type ENUM('all','customer','merchant','deliverer') DEFAULT 'all',
              daily_budget DECIMAL(10,2),
              total_budget DECIMAL(12,2),
              spent_amount DECIMAL(12,2) DEFAULT 0,
              cost_model ENUM('cpm','cpc','fixed') DEFAULT 'cpc',
              cost_per_unit DECIMAL(8,4),
              frequency_cap INT,
              status ENUM('draft','active','paused','completed','cancelled') DEFAULT 'draft',
              start_date DATETIME,
              end_date DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
              FOREIGN KEY (target_category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
              INDEX idx_status (status),
              INDEX idx_merchant_id (merchant_id),
              INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ad_placements (
              id INT AUTO_INCREMENT PRIMARY KEY,
              slug VARCHAR(100) UNIQUE NOT NULL,
              name VARCHAR(255) NOT NULL,
              description TEXT,
              dimensions VARCHAR(50),
              max_ads INT DEFAULT 1,
              is_active BOOLEAN DEFAULT TRUE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_slug (slug)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ad_campaign_placements (
              campaign_id BIGINT NOT NULL,
              placement_id INT NOT NULL,
              PRIMARY KEY (campaign_id, placement_id),
              FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
              FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ad_impressions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              campaign_id BIGINT NOT NULL,
              placement_id INT,
              user_id BIGINT,
              session_id VARCHAR(255),
              ip_address VARCHAR(45),
              user_agent VARCHAR(512),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
              FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE SET NULL,
              INDEX idx_campaign_id (campaign_id),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ad_clicks (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              campaign_id BIGINT NOT NULL,
              impression_id BIGINT,
              placement_id INT,
              user_id BIGINT,
              session_id VARCHAR(255),
              ip_address VARCHAR(45),
              user_agent VARCHAR(512),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
              FOREIGN KEY (impression_id) REFERENCES ad_impressions(id) ON DELETE SET NULL,
              FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE SET NULL,
              INDEX idx_campaign_id (campaign_id),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS ad_clicks");
        $pdo->exec("DROP TABLE IF EXISTS ad_impressions");
        $pdo->exec("DROP TABLE IF EXISTS ad_campaign_placements");
        $pdo->exec("DROP TABLE IF EXISTS ad_placements");
        $pdo->exec("DROP TABLE IF EXISTS ad_campaigns");
    },
];
