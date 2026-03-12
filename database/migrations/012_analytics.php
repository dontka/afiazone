<?php

declare(strict_types=1);

/**
 * Migration: Analytics Events, Audit Logs, API Logs
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS analytics_events (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT,
              event_type ENUM('page_view','product_view','add_to_cart','remove_from_cart','checkout','payment','search','filter','click','scroll') DEFAULT 'page_view',
              event_category VARCHAR(100),
              event_label VARCHAR(255),
              event_value DECIMAL(12,2),
              page_url VARCHAR(512),
              referrer_url VARCHAR(512),
              ip_address VARCHAR(45),
              user_agent TEXT,
              session_id VARCHAR(255),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_user_id (user_id),
              INDEX idx_event_type (event_type),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT,
              action VARCHAR(255),
              entity_type VARCHAR(100),
              entity_id BIGINT,
              old_values JSON,
              new_values JSON,
              ip_address VARCHAR(45),
              user_agent TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
              INDEX idx_entity_type (entity_type),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_logs (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              endpoint VARCHAR(512),
              method VARCHAR(10),
              user_id BIGINT,
              status_code INT,
              response_time_ms INT,
              request_ip VARCHAR(45),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
              INDEX idx_endpoint (endpoint),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS api_logs");
        $pdo->exec("DROP TABLE IF EXISTS audit_logs");
        $pdo->exec("DROP TABLE IF EXISTS analytics_events");
    },
];
