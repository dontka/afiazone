<?php

declare(strict_types=1);

/**
 * Migration: Product Categories, Products, Images, Attributes, Variants, Stocks
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_categories (
              id INT AUTO_INCREMENT PRIMARY KEY,
              parent_id INT,
              name VARCHAR(255) UNIQUE NOT NULL,
              slug VARCHAR(255) UNIQUE,
              description TEXT,
              icon_url VARCHAR(512),
              is_active BOOLEAN DEFAULT TRUE,
              display_order INT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL,
              INDEX idx_slug (slug),
              INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              merchant_id BIGINT,
              sku VARCHAR(100) NOT NULL,
              name VARCHAR(512) NOT NULL,
              slug VARCHAR(512) UNIQUE,
              description LONGTEXT,
              category_id INT,
              price DECIMAL(12,2) NOT NULL,
              cost_price DECIMAL(12,2),
              tax_rate DECIMAL(5,2) DEFAULT 0,
              prescription_required BOOLEAN DEFAULT FALSE,
              is_active BOOLEAN DEFAULT TRUE,
              is_featured BOOLEAN DEFAULT FALSE,
              rating DECIMAL(3,2) DEFAULT 0,
              review_count INT DEFAULT 0,
              status ENUM('draft','published','archived','pending_review') DEFAULT 'draft',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE SET NULL,
              FOREIGN KEY (category_id) REFERENCES product_categories(id),
              INDEX idx_merchant_id (merchant_id),
              INDEX idx_category_id (category_id),
              INDEX idx_is_active (is_active),
              INDEX idx_slug (slug),
              FULLTEXT INDEX ft_search (name, description)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_images (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              product_id BIGINT NOT NULL,
              image_url VARCHAR(512),
              alt_text VARCHAR(255),
              is_primary BOOLEAN DEFAULT FALSE,
              display_order INT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
              INDEX idx_product_id (product_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_attributes (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              product_id BIGINT NOT NULL,
              attribute_name VARCHAR(100),
              attribute_value VARCHAR(255),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_variants (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              product_id BIGINT NOT NULL,
              sku_suffix VARCHAR(50),
              variant_name VARCHAR(255),
              variant_price DECIMAL(12,2),
              stock_quantity INT DEFAULT 0,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchant_stocks (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              merchant_id BIGINT NOT NULL,
              product_id BIGINT NOT NULL,
              variant_id BIGINT,
              quantity INT DEFAULT 0,
              reorder_level INT DEFAULT 10,
              last_restock_date DATETIME,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY unique_stock (merchant_id, product_id, variant_id),
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
              FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
              FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
              INDEX idx_merchant_id (merchant_id),
              INDEX idx_product_id (product_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS merchant_stocks");
        $pdo->exec("DROP TABLE IF EXISTS product_variants");
        $pdo->exec("DROP TABLE IF EXISTS product_attributes");
        $pdo->exec("DROP TABLE IF EXISTS product_images");
        $pdo->exec("DROP TABLE IF EXISTS products");
        $pdo->exec("DROP TABLE IF EXISTS product_categories");
    },
];
