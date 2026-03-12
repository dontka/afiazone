<?php

declare(strict_types=1);

/**
 * Migration: Blog Categories, Posts, Tags, Comments
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_categories (
              id INT AUTO_INCREMENT PRIMARY KEY,
              parent_id INT,
              name VARCHAR(255) NOT NULL,
              slug VARCHAR(255) UNIQUE NOT NULL,
              description TEXT,
              is_active BOOLEAN DEFAULT TRUE,
              display_order INT DEFAULT 0,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (parent_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
              INDEX idx_slug (slug),
              INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_posts (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              author_id BIGINT NOT NULL,
              category_id INT,
              title VARCHAR(512) NOT NULL,
              slug VARCHAR(512) UNIQUE NOT NULL,
              excerpt TEXT,
              content LONGTEXT NOT NULL,
              cover_image_url VARCHAR(512),
              meta_title VARCHAR(255),
              meta_description VARCHAR(512),
              status ENUM('draft','pending_review','published','archived') DEFAULT 'draft',
              is_featured BOOLEAN DEFAULT FALSE,
              view_count BIGINT DEFAULT 0,
              scheduled_at DATETIME,
              published_at DATETIME,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
              INDEX idx_status (status),
              INDEX idx_published_at (published_at),
              INDEX idx_author_id (author_id),
              FULLTEXT INDEX ft_blog_search (title, content)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_tags (
              id INT AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(100) NOT NULL,
              slug VARCHAR(100) UNIQUE NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_slug (slug)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_post_tags (
              post_id BIGINT NOT NULL,
              tag_id INT NOT NULL,
              PRIMARY KEY (post_id, tag_id),
              FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
              FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_comments (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              post_id BIGINT NOT NULL,
              user_id BIGINT NOT NULL,
              parent_id BIGINT,
              content TEXT NOT NULL,
              status ENUM('pending','approved','rejected','spam') DEFAULT 'pending',
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
              FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE,
              INDEX idx_post_id (post_id),
              INDEX idx_status (status),
              INDEX idx_parent_id (parent_id)
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS blog_comments");
        $pdo->exec("DROP TABLE IF EXISTS blog_post_tags");
        $pdo->exec("DROP TABLE IF EXISTS blog_tags");
        $pdo->exec("DROP TABLE IF EXISTS blog_posts");
        $pdo->exec("DROP TABLE IF EXISTS blog_categories");
    },
];
