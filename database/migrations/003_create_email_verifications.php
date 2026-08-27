<?php

declare(strict_types=1);

return static function (PDO $connection): void {
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS email_verifications (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'user_id BIGINT UNSIGNED NOT NULL,' .
        'token_hash CHAR(64) NOT NULL UNIQUE,' .
        'expires_at DATETIME NOT NULL,' .
        'verified_at DATETIME NULL,' .
        'created_at DATETIME NOT NULL,' .
        'INDEX idx_email_verifications_user (user_id),' .
        'INDEX idx_email_verifications_expires (expires_at),' .
        'CONSTRAINT fk_email_verifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
};