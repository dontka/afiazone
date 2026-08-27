<?php

declare(strict_types=1);

return static function (PDO $connection): void {
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS categories (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'parent_id BIGINT UNSIGNED NULL,' .
        'name VARCHAR(160) NOT NULL,' .
        'slug VARCHAR(190) NOT NULL UNIQUE,' .
        'description TEXT NULL,' .
        'status ENUM("draft","published","archived") NOT NULL DEFAULT "published",' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL,' .
        'INDEX idx_categories_parent (parent_id),' .
        'CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS brands (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'name VARCHAR(160) NOT NULL UNIQUE,' .
        'slug VARCHAR(190) NOT NULL UNIQUE,' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS active_ingredients (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'name VARCHAR(160) NOT NULL UNIQUE,' .
        'slug VARCHAR(190) NOT NULL UNIQUE,' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS products (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'uuid CHAR(36) NOT NULL UNIQUE,' .
        'category_id BIGINT UNSIGNED NOT NULL,' .
        'brand_id BIGINT UNSIGNED NULL,' .
        'name VARCHAR(190) NOT NULL,' .
        'slug VARCHAR(190) NOT NULL UNIQUE,' .
        'short_description VARCHAR(255) NULL,' .
        'description TEXT NULL,' .
        'requires_prescription TINYINT(1) NOT NULL DEFAULT 0,' .
        'status ENUM("draft","pending_review","published","archived") NOT NULL DEFAULT "draft",' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL,' .
        'INDEX idx_products_category_status (category_id, status),' .
        'FULLTEXT idx_products_search (name, short_description, description),' .
        'CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),' .
        'CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS product_active_ingredients (' .
        'product_id BIGINT UNSIGNED NOT NULL,' .
        'active_ingredient_id BIGINT UNSIGNED NOT NULL,' .
        'PRIMARY KEY (product_id, active_ingredient_id),' .
        'CONSTRAINT fk_product_ingredients_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,' .
        'CONSTRAINT fk_product_ingredients_ingredient FOREIGN KEY (active_ingredient_id) REFERENCES active_ingredients(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS product_variants (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'product_id BIGINT UNSIGNED NOT NULL,' .
        'name VARCHAR(160) NOT NULL,' .
        'sku VARCHAR(100) NULL UNIQUE,' .
        'created_at DATETIME NOT NULL,' .
        'updated_at DATETIME NOT NULL,' .
        'CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS product_images (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'product_id BIGINT UNSIGNED NOT NULL,' .
        'path VARCHAR(255) NOT NULL,' .
        'alt_text VARCHAR(190) NULL,' .
        'sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,' .
        'created_at DATETIME NOT NULL,' .
        'CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS product_documents (' .
        'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
        'product_id BIGINT UNSIGNED NOT NULL,' .
        'path VARCHAR(255) NOT NULL,' .
        'document_type VARCHAR(80) NOT NULL,' .
        'created_at DATETIME NOT NULL,' .
        'CONSTRAINT fk_product_documents_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $categories = [
        ['Medicaments pharmaceutiques', 'medicaments-pharmaceutiques'],
        ['Produits biologiques', 'produits-biologiques'],
        ['Dispositifs medicaux', 'dispositifs-medicaux'],
        ['Produits de diagnostic', 'produits-diagnostic'],
        ['Soins et pansements', 'soins-pansements'],
        ['Antiseptiques et desinfectants', 'antiseptiques-desinfectants'],
        ['Nutrition medicale', 'nutrition-medicale'],
        ['Medecine traditionnelle', 'medecine-traditionnelle'],
    ];
    $categoryStatement = $connection->prepare('INSERT IGNORE INTO categories (name, slug, created_at, updated_at) VALUES (:name, :slug, NOW(), NOW())');
    foreach ($categories as [$name, $slug]) {
        $categoryStatement->execute(['name' => $name, 'slug' => $slug]);
    }

    $brands = ['AfiaCare', 'MediPlus', 'WellCare', 'HealthLab'];
    $brandStatement = $connection->prepare('INSERT IGNORE INTO brands (name, slug, created_at, updated_at) VALUES (:name, :slug, NOW(), NOW())');
    foreach ($brands as $name) {
        $brandStatement->execute(['name' => $name, 'slug' => strtolower(str_replace(' ', '-', $name))]);
    }
};