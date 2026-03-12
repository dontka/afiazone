<?php

declare(strict_types=1);

/**
 * Migration: Shopping Carts, Orders, Order Items, Status Logs, Delivery Addresses
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shopping_carts (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT,
              session_id VARCHAR(255),
              total_price DECIMAL(12,2) DEFAULT 0,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_user_id (user_id),
              INDEX idx_session_id (session_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shopping_cart_items (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              cart_id BIGINT NOT NULL,
              product_id BIGINT NOT NULL,
              quantity INT NOT NULL DEFAULT 1,
              price_at_add DECIMAL(12,2),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (cart_id) REFERENCES shopping_carts(id) ON DELETE CASCADE,
              FOREIGN KEY (product_id) REFERENCES products(id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_number VARCHAR(50) UNIQUE NOT NULL,
              user_id BIGINT NOT NULL,
              total_amount DECIMAL(14,2),
              tax_amount DECIMAL(12,2) DEFAULT 0,
              shipping_fee DECIMAL(12,2) DEFAULT 0,
              discount_amount DECIMAL(12,2) DEFAULT 0,
              final_amount DECIMAL(14,2),
              payment_method ENUM('cash_on_delivery','wallet','card','mobile_money') DEFAULT 'cash_on_delivery',
              payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
              order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_user_id (user_id),
              INDEX idx_order_status (order_status),
              INDEX idx_payment_status (payment_status),
              INDEX idx_created_at (created_at),
              INDEX idx_order_number (order_number)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL,
              product_id BIGINT NOT NULL,
              quantity INT NOT NULL,
              unit_price DECIMAL(12,2),
              tax_amount DECIMAL(12,2) DEFAULT 0,
              subtotal DECIMAL(14,2),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              FOREIGN KEY (product_id) REFERENCES products(id),
              INDEX idx_order_id (order_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_status_logs (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL,
              previous_status VARCHAR(50),
              new_status VARCHAR(50),
              changed_by BIGINT,
              notes TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              FOREIGN KEY (changed_by) REFERENCES users(id),
              INDEX idx_order_id (order_id),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS delivery_addresses (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL,
              user_id BIGINT NOT NULL,
              recipient_name VARCHAR(255),
              phone_number VARCHAR(20),
              street_address VARCHAR(512),
              city VARCHAR(100),
              state_or_region VARCHAR(100),
              postal_code VARCHAR(20),
              country VARCHAR(100),
              is_default BOOLEAN DEFAULT FALSE,
              latitude DECIMAL(10,8),
              longitude DECIMAL(11,8),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id),
              INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS delivery_addresses");
        $pdo->exec("DROP TABLE IF EXISTS order_status_logs");
        $pdo->exec("DROP TABLE IF EXISTS order_items");
        $pdo->exec("DROP TABLE IF EXISTS orders");
        $pdo->exec("DROP TABLE IF EXISTS shopping_cart_items");
        $pdo->exec("DROP TABLE IF EXISTS shopping_carts");
    },
];
