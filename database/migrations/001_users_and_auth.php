<?php

declare(strict_types=1);

/**
 * Migration: Users, Roles, Permissions, Tokens
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              email VARCHAR(255) UNIQUE NOT NULL,
              phone VARCHAR(20) UNIQUE,
              username VARCHAR(100) UNIQUE,
              unique_id VARCHAR(255) UNIQUE NOT NULL,
              password_hash VARCHAR(255),
              first_name VARCHAR(100),
              last_name VARCHAR(100),
              status ENUM('active','inactive','banned','pending_verification') DEFAULT 'pending_verification',
              email_verified_at DATETIME,
              phone_verified_at DATETIME,
              last_login_at DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              INDEX idx_email (email),
              INDEX idx_phone (phone),
              INDEX idx_username (username),
              INDEX idx_unique_id (unique_id),
              INDEX idx_status (status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
              id INT AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(50) UNIQUE NOT NULL,
              description TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_roles (
              user_id BIGINT,
              role_id INT,
              assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (user_id, role_id),
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS permissions (
              id INT AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(100) UNIQUE NOT NULL,
              description TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS role_permissions (
              role_id INT,
              permission_id INT,
              PRIMARY KEY (role_id, permission_id),
              FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
              FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tokens (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT,
              token_type ENUM('email_verification','password_reset','jwt','api','two_factor') DEFAULT 'api',
              token_hash VARCHAR(255) UNIQUE NOT NULL,
              expires_at DATETIME,
              is_used BOOLEAN DEFAULT FALSE,
              ip_address VARCHAR(45),
              user_agent TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_token_hash (token_hash),
              INDEX idx_expires_at (expires_at),
              INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS tokens");
        $pdo->exec("DROP TABLE IF EXISTS role_permissions");
        $pdo->exec("DROP TABLE IF EXISTS permissions");
        $pdo->exec("DROP TABLE IF EXISTS user_roles");
        $pdo->exec("DROP TABLE IF EXISTS roles");
        $pdo->exec("DROP TABLE IF EXISTS users");
    },
];
