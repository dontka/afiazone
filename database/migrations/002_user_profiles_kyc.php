<?php

declare(strict_types=1);

/**
 * Migration: User Profiles & KYC
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_profiles (
              user_id BIGINT PRIMARY KEY,
              bio TEXT,
              avatar_url VARCHAR(512),
              country VARCHAR(100),
              city VARCHAR(100),
              address VARCHAR(512),
              postal_code VARCHAR(20),
              preferred_locale VARCHAR(10) DEFAULT 'fr',
              company_name VARCHAR(255),
              company_type VARCHAR(100),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS kyc_submissions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL UNIQUE,
              status ENUM('pending','approved','rejected','revision_requested') DEFAULT 'pending',
              submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
              review_date DATETIME,
              reviewer_id BIGINT,
              rejection_reason TEXT,
              internal_notes TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (reviewer_id) REFERENCES users(id),
              INDEX idx_status (status),
              INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS kyc_documents (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              kyc_submission_id BIGINT NOT NULL,
              document_type ENUM('id_card','passport','national_id','driver_license','proof_of_address','business_license','tax_certificate') DEFAULT 'id_card',
              file_url VARCHAR(512),
              file_name VARCHAR(255),
              mime_type VARCHAR(50),
              file_size BIGINT,
              verification_status ENUM('pending','verified','rejected') DEFAULT 'pending',
              uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              verified_at DATETIME,
              FOREIGN KEY (kyc_submission_id) REFERENCES kyc_submissions(id) ON DELETE CASCADE,
              INDEX idx_verification_status (verification_status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS kyc_documents");
        $pdo->exec("DROP TABLE IF EXISTS kyc_submissions");
        $pdo->exec("DROP TABLE IF EXISTS user_profiles");
    },
];
