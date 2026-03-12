<?php

declare(strict_types=1);

/**
 * Migration: Notifications, Reports, Support Tickets & Messages
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL,
              notification_type ENUM('order_status','payment','promotion','system','support','alert') DEFAULT 'system',
              title VARCHAR(255),
              message TEXT,
              is_read BOOLEAN DEFAULT FALSE,
              action_url VARCHAR(512),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              read_at DATETIME,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              INDEX idx_user_id (user_id),
              INDEX idx_is_read (is_read),
              INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reports (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              report_type ENUM('product','merchant','user','review','prescription','order') DEFAULT 'product',
              reported_item_id VARCHAR(50),
              report_reason ENUM('inappropriate_content','fake_product','damaged_on_arrival','non_delivery','suspicious_activity','spam','medical_concern','fraud') DEFAULT 'inappropriate_content',
              reporter_id BIGINT,
              description TEXT,
              status ENUM('pending','investigating','resolved','dismissed') DEFAULT 'pending',
              assigned_to BIGINT,
              resolution_notes TEXT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              resolved_at DATETIME,
              FOREIGN KEY (reporter_id) REFERENCES users(id),
              FOREIGN KEY (assigned_to) REFERENCES users(id),
              INDEX idx_status (status),
              INDEX idx_report_type (report_type)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS support_tickets (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT NOT NULL,
              ticket_number VARCHAR(50) UNIQUE,
              subject VARCHAR(255),
              description TEXT,
              category ENUM('billing','shipping','product_issue','account','general','medical_concern') DEFAULT 'general',
              priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
              status ENUM('open','in_progress','waiting_for_customer','resolved','closed') DEFAULT 'open',
              assigned_to BIGINT,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              closed_at DATETIME,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (assigned_to) REFERENCES users(id),
              INDEX idx_status (status),
              INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS support_messages (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              ticket_id BIGINT NOT NULL,
              user_id BIGINT,
              message TEXT,
              attachment_url VARCHAR(512),
              is_internal BOOLEAN DEFAULT FALSE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id),
              INDEX idx_ticket_id (ticket_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS support_messages");
        $pdo->exec("DROP TABLE IF EXISTS support_tickets");
        $pdo->exec("DROP TABLE IF EXISTS reports");
        $pdo->exec("DROP TABLE IF EXISTS notifications");
    },
];
