<?php

declare(strict_types=1);

/**
 * Migration: Add multi-authentication fields (username, unique_id)
 * Allows users to login via email, phone, username, or unique_id
 */

return [
    'up' => function (\PDO $pdo): void {
        // Check if columns already exist before adding them
        $table = $pdo->query("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'users' 
            AND TABLE_SCHEMA = DATABASE()
        ")->fetchAll(\PDO::FETCH_COLUMN);

        // Add username column if it doesn't exist
        if (!in_array('username', $table, true)) {
            $pdo->exec("
                ALTER TABLE users 
                ADD COLUMN username VARCHAR(100) UNIQUE,
                ADD INDEX idx_username (username)
            ");
        }

        // Add unique_id column if it doesn't exist
        if (!in_array('unique_id', $table, true)) {
            // Step 1: Add column as nullable first (no UNIQUE constraint yet)
            $pdo->exec("
                ALTER TABLE users 
                ADD COLUMN unique_id VARCHAR(255)
            ");

            // Step 2: Generate UUID for each existing user
            $users = $pdo->query("SELECT id FROM users WHERE unique_id IS NULL")->fetchAll(\PDO::FETCH_COLUMN);
            $updateStmt = $pdo->prepare("UPDATE users SET unique_id = ? WHERE id = ?");

            foreach ($users as $userId) {
                $uniqueId = self::generateUUID();
                $updateStmt->execute([$uniqueId, $userId]);
            }

            // Step 3: Add UNIQUE constraint and index
            $pdo->exec("
                ALTER TABLE users 
                ADD UNIQUE KEY unique_id (unique_id),
                ADD INDEX idx_unique_id (unique_id)
            ");

            // Step 4: Modify column to NOT NULL
            $pdo->exec("
                ALTER TABLE users 
                MODIFY COLUMN unique_id VARCHAR(255) NOT NULL
            ");
        }
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS username");
        $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS unique_id");
    },
];

/**
 * Generate UUID v4
 */
function generateUUID(): string
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
