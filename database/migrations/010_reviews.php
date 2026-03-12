<?php

declare(strict_types=1);

/**
 * Migration: Product Reviews, Merchant Reviews, Delivery Reviews
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_reviews (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              product_id BIGINT NOT NULL,
              user_id BIGINT NOT NULL,
              order_id BIGINT,
              rating INT DEFAULT 5,
              title VARCHAR(255),
              comment TEXT,
              is_verified_purchase BOOLEAN DEFAULT TRUE,
              helpful_count INT DEFAULT 0,
              status ENUM('pending','approved','rejected','flagged') DEFAULT 'pending',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
              INDEX idx_product_id (product_id),
              INDEX idx_rating (rating)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchant_reviews (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              merchant_id BIGINT NOT NULL,
              user_id BIGINT NOT NULL,
              order_id BIGINT,
              rating INT DEFAULT 5,
              comment TEXT,
              is_verified_purchase BOOLEAN DEFAULT TRUE,
              service_rating INT,
              delivery_rating INT,
              packaging_rating INT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
              INDEX idx_merchant_id (merchant_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS delivery_reviews (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              delivery_personnel_id BIGINT NOT NULL,
              user_id BIGINT NOT NULL,
              shipment_id BIGINT,
              rating INT DEFAULT 5,
              comment TEXT,
              punctuality_rating INT,
              professionalism_rating INT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (delivery_personnel_id) REFERENCES delivery_personnel(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE SET NULL,
              INDEX idx_delivery_personnel_id (delivery_personnel_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS delivery_reviews");
        $pdo->exec("DROP TABLE IF EXISTS merchant_reviews");
        $pdo->exec("DROP TABLE IF EXISTS product_reviews");
    },
];
