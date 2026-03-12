<?php

declare(strict_types=1);

/**
 * Migration: Default seed data (roles, permissions, categories, languages, placements, blog categories)
 */

return [
    'up' => function (\PDO $pdo): void {
        // ── Roles ──
        $pdo->exec("
            INSERT INTO roles (name, description) VALUES
            ('admin', 'Administrator with full access'),
            ('moderator', 'Moderator for content management'),
            ('merchant', 'Merchant selling on marketplace'),
            ('customer', 'Regular customer'),
            ('deliverer', 'Delivery personnel'),
            ('partner', 'Partner organization')
            ON DUPLICATE KEY UPDATE name=name
        ");

        // ── Permissions ──
        $pdo->exec("
            INSERT INTO permissions (name, description) VALUES
            ('manage_users', 'Create, read, update, delete users'),
            ('manage_roles', 'Manage roles and permissions'),
            ('manage_products', 'CRUD products'),
            ('create_product', 'Create a product'),
            ('update_product', 'Update a product'),
            ('delete_product', 'Delete a product'),
            ('view_products', 'View product catalog'),
            ('manage_orders', 'Manage all orders'),
            ('create_order', 'Place an order'),
            ('update_order', 'Update order status'),
            ('view_orders', 'View orders'),
            ('cancel_order', 'Cancel an order'),
            ('manage_wallet', 'Manage wallet system'),
            ('view_wallet', 'View own wallet'),
            ('topup_wallet', 'Top up wallet'),
            ('transfer_funds', 'Transfer wallet funds'),
            ('manage_kyc', 'Review KYC submissions'),
            ('submit_kyc', 'Submit KYC documents'),
            ('manage_merchants', 'Manage merchant accounts'),
            ('manage_deliveries', 'Manage delivery assignments'),
            ('view_analytics', 'View platform analytics'),
            ('manage_prescriptions', 'Verify prescriptions'),
            ('manage_reports', 'Handle reports and flags'),
            ('manage_support', 'Handle support tickets'),
            ('manage_promotions', 'Manage promotion codes'),
            ('manage_blog', 'Create, edit, delete blog posts'),
            ('moderate_comments', 'Moderate blog comments'),
            ('manage_ads', 'Manage advertising campaigns'),
            ('manage_api_clients', 'Manage third-party API clients'),
            ('manage_translations', 'Manage translations and languages'),
            ('manage_languages', 'Add/remove supported languages')
            ON DUPLICATE KEY UPDATE name=name
        ");

        // ── Role-Permission mappings ──
        // Admin gets all permissions
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin'
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Customer permissions
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'customer' AND p.name IN ('view_products','create_order','view_orders','cancel_order','view_wallet','topup_wallet','transfer_funds','submit_kyc')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Merchant permissions
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'merchant' AND p.name IN ('view_products','create_product','update_product','delete_product','view_orders','update_order','view_wallet','topup_wallet','transfer_funds','submit_kyc','manage_ads')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Moderator permissions
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'moderator' AND p.name IN ('manage_users','view_products','manage_orders','manage_kyc','manage_prescriptions','manage_reports','manage_support','view_analytics','manage_blog','moderate_comments')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // Deliverer permissions
        $pdo->exec("
            INSERT INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = 'deliverer' AND p.name IN ('view_orders','view_wallet','topup_wallet','transfer_funds','submit_kyc')
            ON DUPLICATE KEY UPDATE role_id=role_id
        ");

        // ── Product Categories ──
        $pdo->exec("
            INSERT INTO product_categories (name, slug, description, is_active) VALUES
            ('Médicaments', 'medicaments', 'Produits pharmaceutiques', TRUE),
            ('Dispositifs Médicaux', 'dispositifs-medicaux', 'Équipements médicaux', TRUE),
            ('Vitamines & Suppléments', 'vitamines-supplements', 'Vitamines et compléments nutritionnels', TRUE),
            ('Soins & Pansements', 'soins-pansements', 'Produits de soin et pansements', TRUE),
            ('Équipement Médical', 'equipement-medical', 'Équipement et appareils médicaux', TRUE)
            ON DUPLICATE KEY UPDATE slug=slug
        ");

        // ── Languages ──
        $pdo->exec("
            INSERT INTO languages (code, name, native_name, flag_icon, is_default, is_active, is_rtl, display_order) VALUES
            ('fr', 'Français', 'Français', 'fi-fr', TRUE, TRUE, FALSE, 1),
            ('en', 'Anglais', 'English', 'fi-gb', FALSE, TRUE, FALSE, 2),
            ('sw', 'Swahili', 'Kiswahili', 'fi-tz', FALSE, TRUE, FALSE, 3)
            ON DUPLICATE KEY UPDATE code=code
        ");

        // ── Ad Placements ──
        $pdo->exec("
            INSERT INTO ad_placements (slug, name, description, dimensions, max_ads, is_active) VALUES
            ('homepage_banner', 'Bannière page d''accueil', 'Grande bannière en haut de la page d''accueil', '1200x400', 1, TRUE),
            ('category_sidebar', 'Sidebar catégorie', 'Publicité dans la sidebar des pages catégorie', '300x250', 2, TRUE),
            ('product_detail_related', 'Produit sponsorisé', 'Suggestion de produit sponsorisé sur la page détail', '300x250', 1, TRUE),
            ('search_results_top', 'Haut des résultats', 'Publicité en haut des résultats de recherche', '728x90', 1, TRUE),
            ('checkout_suggestion', 'Suggestion checkout', 'Suggestion de produit au moment du checkout', '300x250', 1, TRUE),
            ('blog_inline', 'Dans les articles', 'Publicité intégrée dans les articles de blog', '728x90', 1, TRUE)
            ON DUPLICATE KEY UPDATE slug=slug
        ");

        // ── Blog Categories ──
        $pdo->exec("
            INSERT INTO blog_categories (name, slug, description, is_active) VALUES
            ('Santé & Bien-être', 'sante-bien-etre', 'Articles sur la santé générale et le bien-être', TRUE),
            ('Actualités Médicales', 'actualites-medicales', 'Dernières nouvelles du monde médical', TRUE),
            ('Conseils Pharmacie', 'conseils-pharmacie', 'Conseils et astuces pharmaceutiques', TRUE),
            ('Nutrition', 'nutrition', 'Articles sur la nutrition et l''alimentation', TRUE),
            ('Prévention', 'prevention', 'Prévention des maladies et hygiène de vie', TRUE)
            ON DUPLICATE KEY UPDATE slug=slug
        ");
    },

    'down' => function (\PDO $pdo): void {
        // Seed data — reverse insertion order
        $pdo->exec("DELETE FROM blog_categories WHERE slug IN ('sante-bien-etre','actualites-medicales','conseils-pharmacie','nutrition','prevention')");
        $pdo->exec("DELETE FROM ad_placements WHERE slug IN ('homepage_banner','category_sidebar','product_detail_related','search_results_top','checkout_suggestion','blog_inline')");
        $pdo->exec("DELETE FROM languages WHERE code IN ('fr','en','sw')");
        $pdo->exec("DELETE FROM product_categories WHERE slug IN ('medicaments','dispositifs-medicaux','vitamines-supplements','soins-pansements','equipement-medical')");
        $pdo->exec("DELETE FROM role_permissions");
        $pdo->exec("DELETE FROM permissions");
        $pdo->exec("DELETE FROM roles");
    },
];
