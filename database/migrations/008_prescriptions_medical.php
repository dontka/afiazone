<?php

declare(strict_types=1);

/**
 * Migration: Prescriptions, Medical Records, Consultations
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS prescriptions (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL,
              user_id BIGINT NOT NULL,
              prescriber_name VARCHAR(255),
              prescriber_license VARCHAR(100),
              prescriber_contact VARCHAR(100),
              image_url VARCHAR(512),
              prescription_date DATE,
              expiry_date DATE,
              verification_status ENUM('pending','verified','rejected','expired') DEFAULT 'pending',
              verified_by BIGINT,
              verification_date DATETIME,
              rejection_reason TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (verified_by) REFERENCES users(id),
              INDEX idx_user_id (user_id),
              INDEX idx_verification_status (verification_status)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS prescription_verification_logs (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              prescription_id BIGINT NOT NULL,
              verified_by BIGINT,
              status VARCHAR(50),
              notes TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
              FOREIGN KEY (verified_by) REFERENCES users(id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS medical_records (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL,
              record_type ENUM('diagnosis','treatment','lab_result','vaccination','consultation','surgery','allergy','medication') DEFAULT 'consultation',
              title VARCHAR(255),
              description LONGTEXT,
              provider_name VARCHAR(255),
              provider_facility VARCHAR(255),
              recorded_date DATETIME,
              file_url VARCHAR(512),
              is_shared_with_all_providers BOOLEAN DEFAULT FALSE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_user_id (user_id),
              INDEX idx_record_type (record_type)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS medical_record_access (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              medical_record_id BIGINT NOT NULL,
              authorized_user_id BIGINT NOT NULL,
              access_type ENUM('view','view_edit') DEFAULT 'view',
              expires_at DATETIME,
              granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
              FOREIGN KEY (authorized_user_id) REFERENCES users(id) ON DELETE CASCADE,
              UNIQUE KEY unique_access (medical_record_id, authorized_user_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS consultations (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL,
              doctor_id BIGINT,
              appointment_date DATETIME,
              appointment_type ENUM('online','in_person') DEFAULT 'in_person',
              status ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
              notes LONGTEXT,
              prescription_id BIGINT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (doctor_id) REFERENCES users(id),
              FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
              INDEX idx_user_id (user_id),
              INDEX idx_appointment_date (appointment_date)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS consultations");
        $pdo->exec("DROP TABLE IF EXISTS medical_record_access");
        $pdo->exec("DROP TABLE IF EXISTS medical_records");
        $pdo->exec("DROP TABLE IF EXISTS prescription_verification_logs");
        $pdo->exec("DROP TABLE IF EXISTS prescriptions");
    },
];
