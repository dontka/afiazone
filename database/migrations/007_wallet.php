<?php

declare(strict_types=1);

/**
 * Migration: Wallets, Transactions, Balance History, Topups, Reservations
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallets (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT UNIQUE NOT NULL,
              currency VARCHAR(3) DEFAULT 'USD',
              balance DECIMAL(14,2) DEFAULT 0,
              reserved_balance DECIMAL(14,2) DEFAULT 0,
              available_balance DECIMAL(14,2) DEFAULT 0,
              total_received DECIMAL(14,2) DEFAULT 0,
              total_spent DECIMAL(14,2) DEFAULT 0,
              status ENUM('active','frozen','suspended') DEFAULT 'active',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_user_id (user_id),
              INDEX idx_status (status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallet_transactions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              wallet_id BIGINT NOT NULL,
              transaction_type ENUM('credit','debit','reserve','release') DEFAULT 'debit',
              amount DECIMAL(14,2) NOT NULL,
              balance_before DECIMAL(14,2),
              balance_after DECIMAL(14,2),
              external_reference VARCHAR(255),
              payment_method ENUM('wallet','card','mobile_money','bank_transfer','cash','bonus') DEFAULT 'wallet',
              description TEXT,
              metadata JSON,
              status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
              INDEX idx_wallet_id (wallet_id),
              INDEX idx_status (status),
              INDEX idx_external_reference (external_reference),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallet_balance_history (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              wallet_id BIGINT NOT NULL,
              balance DECIMAL(14,2),
              reserved_balance DECIMAL(14,2),
              available_balance DECIMAL(14,2),
              snapshot_date DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
              INDEX idx_wallet_id (wallet_id),
              INDEX idx_snapshot_date (snapshot_date)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallet_topups (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              wallet_id BIGINT NOT NULL,
              amount DECIMAL(14,2),
              payment_method ENUM('card','mobile_money','bank_transfer') DEFAULT 'mobile_money',
              external_transaction_id VARCHAR(255),
              status ENUM('pending','completed','failed') DEFAULT 'pending',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              completed_at DATETIME,
              FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
              INDEX idx_wallet_id (wallet_id),
              INDEX idx_status (status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallet_reservations (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              wallet_id BIGINT NOT NULL,
              order_id BIGINT,
              amount DECIMAL(14,2),
              reason ENUM('order_payment','merchant_settlement','fee','refund') DEFAULT 'order_payment',
              status ENUM('reserved','released','consumed') DEFAULT 'reserved',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              released_at DATETIME,
              FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
              INDEX idx_wallet_id (wallet_id),
              INDEX idx_status (status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS wallet_reservations");
        $pdo->exec("DROP TABLE IF EXISTS wallet_topups");
        $pdo->exec("DROP TABLE IF EXISTS wallet_balance_history");
        $pdo->exec("DROP TABLE IF EXISTS wallet_transactions");
        $pdo->exec("DROP TABLE IF EXISTS wallets");
    },
];
