<?php

declare(strict_types=1);

return static function (PDO $connection): void {
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS user_profiles (' .
        'user_id BIGINT UNSIGNED PRIMARY KEY,' .
        'full_name VARCHAR(160) NOT NULL,' .
        'business_name VARCHAR(190) NULL,' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL,' .
        'CONSTRAINT fk_user_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS password_resets (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'user_id BIGINT UNSIGNED NOT NULL,' .
        'token_hash CHAR(64) NOT NULL UNIQUE,' .
        'expires_at DATETIME NOT NULL,' .
        'used_at DATETIME NULL,' .
        'created_at DATETIME NOT NULL,' .
        'INDEX idx_password_resets_user (user_id),' .
        'INDEX idx_password_resets_expires (expires_at),' .
        'CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS login_attempts (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'identifier VARCHAR(190) NOT NULL,' .
        'ip_address VARCHAR(45) NOT NULL,' .
        'successful TINYINT(1) NOT NULL DEFAULT 0,' .
        'attempted_at DATETIME NOT NULL,' .
        'INDEX idx_login_attempts_lookup (identifier, ip_address, attempted_at)' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS user_sessions (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'user_id BIGINT UNSIGNED NOT NULL,' .
        'session_hash CHAR(64) NOT NULL UNIQUE,' .
        'ip_address VARCHAR(45) NULL,' .
        'user_agent VARCHAR(255) NULL,' .
        'last_activity_at DATETIME NOT NULL,' .
        'created_at DATETIME NOT NULL,' .
        'INDEX idx_user_sessions_user (user_id),' .
        'CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS account_levels (' .
        'user_id BIGINT UNSIGNED PRIMARY KEY,' .
        "level VARCHAR(40) NOT NULL DEFAULT 'verified'," .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL,' .
        'CONSTRAINT fk_account_levels_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $roles = [
        ['super_admin', 'Super administrateur'],
        ['admin', 'Administrateur'],
        ['moderator', 'Moderateur'],
        ['merchant', 'Marchand'],
        ['customer', 'Client'],
        ['courier', 'Livreur'],
        ['partner', 'Partenaire'],
    ];
    $roleStatement = $connection->prepare('INSERT IGNORE INTO roles (name, label) VALUES (:name, :label)');
    foreach ($roles as [$name, $label]) {
        $roleStatement->execute(['name' => $name, 'label' => $label]);
    }

    $permissions = [
        ['admin.access', 'Acceder a l administration'],
        ['users.manage', 'Gerer les utilisateurs'],
        ['merchant.manage', 'Gerer les marchands'],
    ];
    $permissionStatement = $connection->prepare('INSERT IGNORE INTO permissions (name, label) VALUES (:name, :label)');
    foreach ($permissions as [$name, $label]) {
        $permissionStatement->execute(['name' => $name, 'label' => $label]);
    }
};