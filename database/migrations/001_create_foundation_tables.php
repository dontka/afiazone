<?php

declare(strict_types=1);

return static function (PDO $connection): void {
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS users (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'uuid CHAR(36) NOT NULL UNIQUE,' .
        'email VARCHAR(190) NULL UNIQUE,' .
        'phone VARCHAR(30) NULL UNIQUE,' .
        'password_hash VARCHAR(255) NOT NULL,' .
        "status ENUM('pending','active','suspended','deleted') NOT NULL DEFAULT 'pending'," .
        'email_verified_at DATETIME NULL,' .
        'phone_verified_at DATETIME NULL,' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS roles (' .
        'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'name VARCHAR(80) NOT NULL UNIQUE,' .
        'label VARCHAR(120) NOT NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS permissions (' .
        'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'name VARCHAR(120) NOT NULL UNIQUE,' .
        'label VARCHAR(160) NOT NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS role_permissions (' .
        'role_id INT UNSIGNED NOT NULL,' .
        'permission_id INT UNSIGNED NOT NULL,' .
        'PRIMARY KEY (role_id, permission_id),' .
        'CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,' .
        'CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS user_roles (' .
        'user_id BIGINT UNSIGNED NOT NULL,' .
        'role_id INT UNSIGNED NOT NULL,' .
        'PRIMARY KEY (user_id, role_id),' .
        'CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,' .
        'CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS audit_logs (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'actor_id BIGINT UNSIGNED NULL,' .
        'action VARCHAR(160) NOT NULL,' .
        'entity_type VARCHAR(120) NULL,' .
        'entity_id BIGINT UNSIGNED NULL,' .
        'ip_address VARCHAR(45) NULL,' .
        'user_agent VARCHAR(255) NULL,' .
        'metadata JSON NULL,' .
        'created_at DATETIME NOT NULL,' .
        'INDEX idx_audit_logs_actor_id (actor_id),' .
        'INDEX idx_audit_logs_entity (entity_type, entity_id),' .
        'INDEX idx_audit_logs_created_at (created_at)' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
};