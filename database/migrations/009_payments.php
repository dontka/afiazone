<?php

declare(strict_types=1);

/**
 * Migration: Payment Methods, Transactions, Reconciliations, Refunds
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_payment_methods (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL,
              payment_method_type ENUM('card','mobile_money','bank_account','wallet') DEFAULT 'card',
              name VARCHAR(100),
              gateway_id VARCHAR(255),
              last_four VARCHAR(4),
              is_default BOOLEAN DEFAULT FALSE,
              is_verified BOOLEAN DEFAULT FALSE,
              expires_at DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payment_transactions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL,
              amount DECIMAL(14,2),
              currency VARCHAR(3) DEFAULT 'USD',
              payment_method ENUM('wallet','card','mobile_money','cash_on_delivery') DEFAULT 'wallet',
              payment_gateway VARCHAR(50),
              gateway_transaction_id VARCHAR(255),
              status ENUM('initiated','processing','completed','failed','refunded','cancelled') DEFAULT 'initiated',
              payment_date DATETIME,
              completion_date DATETIME,
              failure_reason TEXT,
              metadata JSON,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              INDEX idx_order_id (order_id),
              INDEX idx_status (status),
              INDEX idx_gateway_transaction_id (gateway_transaction_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payment_reconciliations (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              payment_transaction_id BIGINT NOT NULL,
              gateway_receipt_reference VARCHAR(255),
              settlement_status ENUM('pending','settled','failed','disputed') DEFAULT 'pending',
              settled_amount DECIMAL(14,2),
              settled_date DATETIME,
              fees DECIMAL(10,2),
              notes TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id) ON DELETE CASCADE,
              INDEX idx_payment_transaction_id (payment_transaction_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS refunds (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL,
              payment_transaction_id BIGINT,
              amount DECIMAL(14,2),
              reason ENUM('customer_request','payment_failed','item_unavailable','damaged_item','prescription_rejected','order_cancelled') DEFAULT 'customer_request',
              status ENUM('pending','processing','completed','failed','rejected') DEFAULT 'pending',
              processed_by BIGINT,
              processed_date DATETIME,
              refund_method ENUM('original_method','wallet','bank_transfer') DEFAULT 'original_method',
              notes TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id),
              FOREIGN KEY (processed_by) REFERENCES users(id),
              INDEX idx_order_id (order_id),
              INDEX idx_status (status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS refunds");
        $pdo->exec("DROP TABLE IF EXISTS payment_reconciliations");
        $pdo->exec("DROP TABLE IF EXISTS payment_transactions");
        $pdo->exec("DROP TABLE IF EXISTS user_payment_methods");
    },
];
