<?php

declare(strict_types=1);

/**
 * Migration: Delivery Providers, Personnel, Shipments, Tracking
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS delivery_providers (
              id INT AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(255) UNIQUE,
              api_endpoint VARCHAR(512),
              api_key_encrypted VARCHAR(512),
              contact_phone VARCHAR(20),
              is_active BOOLEAN DEFAULT TRUE,
              average_delivery_days INT,
              base_fee DECIMAL(10,2),
              per_km_fee DECIMAL(8,2),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS delivery_personnel (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT UNIQUE NOT NULL,
              provider_id INT,
              license_type VARCHAR(50),
              license_number VARCHAR(100),
              license_expiry DATE,
              vehicle_type VARCHAR(100),
              vehicle_license_plate VARCHAR(50),
              is_available BOOLEAN DEFAULT TRUE,
              current_location_lat DECIMAL(10,8),
              current_location_lon DECIMAL(11,8),
              last_location_update DATETIME,
              tier_id INT DEFAULT 1,
              average_rating DECIMAL(3,2) DEFAULT 0,
              total_deliveries INT DEFAULT 0,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (provider_id) REFERENCES delivery_providers(id),
              INDEX idx_is_available (is_available)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shipments (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              order_id BIGINT NOT NULL UNIQUE,
              tracking_number VARCHAR(100) UNIQUE,
              delivery_personnel_id BIGINT,
              provider_id INT,
              status ENUM('pending','assigned','picked_up','in_transit','attempted','delivered','failed','cancelled') DEFAULT 'pending',
              estimated_delivery_date DATETIME,
              actual_delivery_date DATETIME,
              delivery_code VARCHAR(10),
              qr_code_url VARCHAR(512),
              pickup_location VARCHAR(255),
              signature_required BOOLEAN DEFAULT TRUE,
              recipient_signature_url VARCHAR(512),
              delivery_notes TEXT,
              delivery_proof_photo_url VARCHAR(512),
              failed_attempt_reason TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
              FOREIGN KEY (delivery_personnel_id) REFERENCES delivery_personnel(id),
              FOREIGN KEY (provider_id) REFERENCES delivery_providers(id),
              INDEX idx_order_id (order_id),
              INDEX idx_status (status),
              INDEX idx_tracking_number (tracking_number)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shipment_tracking_logs (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              shipment_id BIGINT NOT NULL,
              status VARCHAR(50),
              location VARCHAR(255),
              notes TEXT,
              gps_lat DECIMAL(10,8),
              gps_lon DECIMAL(11,8),
              timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
              INDEX idx_shipment_id (shipment_id),
              INDEX idx_timestamp (timestamp)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS shipment_tracking_logs");
        $pdo->exec("DROP TABLE IF EXISTS shipments");
        $pdo->exec("DROP TABLE IF EXISTS delivery_personnel");
        $pdo->exec("DROP TABLE IF EXISTS delivery_providers");
    },
];
