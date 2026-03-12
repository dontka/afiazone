<?php

declare(strict_types=1);

/**
 * Migration: API Clients, Permissions, Webhooks, Webhook Logs
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_clients (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              merchant_id BIGINT,
              name VARCHAR(255) NOT NULL,
              description TEXT,
              api_key VARCHAR(255) UNIQUE NOT NULL,
              api_secret_hash VARCHAR(255) NOT NULL,
              environment ENUM('sandbox','production') DEFAULT 'sandbox',
              is_active BOOLEAN DEFAULT TRUE,
              requests_per_minute INT DEFAULT 60,
              requests_per_day INT DEFAULT 10000,
              allowed_ips JSON,
              last_used_at DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE SET NULL,
              INDEX idx_api_key (api_key),
              INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_client_permissions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              api_client_id BIGINT NOT NULL,
              permission VARCHAR(100) NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE,
              UNIQUE KEY unique_client_perm (api_client_id, permission)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_webhooks (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              api_client_id BIGINT NOT NULL,
              url VARCHAR(512) NOT NULL,
              secret_hash VARCHAR(255) NOT NULL,
              events JSON NOT NULL,
              is_active BOOLEAN DEFAULT TRUE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE,
              INDEX idx_api_client_id (api_client_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_webhook_logs (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              webhook_id BIGINT NOT NULL,
              event_type VARCHAR(100) NOT NULL,
              payload JSON,
              response_status_code INT,
              response_body TEXT,
              latency_ms INT,
              attempt INT DEFAULT 1,
              status ENUM('success','failed','pending') DEFAULT 'pending',
              next_retry_at DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (webhook_id) REFERENCES api_webhooks(id) ON DELETE CASCADE,
              INDEX idx_webhook_id (webhook_id),
              INDEX idx_status (status),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS api_webhook_logs");
        $pdo->exec("DROP TABLE IF EXISTS api_webhooks");
        $pdo->exec("DROP TABLE IF EXISTS api_client_permissions");
        $pdo->exec("DROP TABLE IF EXISTS api_clients");
    },
];
