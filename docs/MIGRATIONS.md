# Migrations — AfiaZone

## Vue d'ensemble

Le système de migrations permet de versionner la structure de la base de données. Chaque migration est un fichier PHP dans `database/migrations/` qui retourne un tableau associatif `['up' => fn(PDO), 'down' => fn(PDO)]`.

Les migrations sont exécutées dans l'ordre alphabétique de leur nom de fichier (préfixe numérique).

## Commandes

```bash
# Exécuter toutes les migrations en attente
php bin/migrate.php

# Voir le statut de chaque migration
php bin/migrate.php status

# Annuler le dernier batch
php bin/migrate.php down

# Annuler les N derniers batches
php bin/migrate.php down 3

# Annuler TOUTES les migrations
php bin/migrate.php reset

# Drop tout + re-migrer (destructif)
php bin/migrate.php fresh

# Créer une nouvelle migration
php bin/migrate.php create nom_de_la_migration
```

## Format d'un fichier de migration

```php
<?php

declare(strict_types=1);

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS example (
              id BIGINT AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(255) NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS example");
    },
];
```

## Migrations existantes

| # | Fichier | Module | Tables |
|---|---------|--------|--------|
| 001 | `001_users_and_auth.php` | Utilisateurs & Auth | `users`, `roles`, `user_roles`, `permissions`, `role_permissions`, `tokens` |
| 002 | `002_user_profiles_kyc.php` | Profils & KYC | `user_profiles`, `kyc_submissions`, `kyc_documents` |
| 003 | `003_merchants.php` | Marchands | `merchant_tiers`, `merchants`, `merchant_shipping_info`, `merchant_fees` |
| 004 | `004_products_catalog.php` | Produits & Catalogue | `product_categories`, `products`, `product_images`, `product_attributes`, `product_variants`, `merchant_stocks` |
| 005 | `005_orders_cart.php` | Commandes & Panier | `shopping_carts`, `shopping_cart_items`, `orders`, `order_items`, `order_status_logs`, `delivery_addresses` |
| 006 | `006_delivery.php` | Livraison | `delivery_providers`, `delivery_personnel`, `shipments`, `shipment_tracking_logs` |
| 007 | `007_wallet.php` | Portefeuille | `wallets`, `wallet_transactions`, `wallet_balance_history`, `wallet_topups`, `wallet_reservations` |
| 008 | `008_prescriptions_medical.php` | Médical | `prescriptions`, `prescription_verification_logs`, `medical_records`, `medical_record_access`, `consultations` |
| 009 | `009_payments.php` | Paiements | `user_payment_methods`, `payment_transactions`, `payment_reconciliations`, `refunds` |
| 010 | `010_reviews.php` | Avis | `product_reviews`, `merchant_reviews`, `delivery_reviews` |
| 011 | `011_notifications_support.php` | Notifications & Support | `notifications`, `reports`, `support_tickets`, `support_messages` |
| 012 | `012_analytics.php` | Analytique | `analytics_events`, `audit_logs`, `api_logs` |
| 013 | `013_promotions_insurance.php` | Promotions & Assurance | `promotion_codes`, `promotion_code_usages`, `partnerships`, `insurance_plans`, `insurance_subscriptions` |
| 014 | `014_blog.php` | Blog | `blog_categories`, `blog_posts`, `blog_tags`, `blog_post_tags`, `blog_comments` |
| 015 | `015_advertising.php` | Publicité | `ad_campaigns`, `ad_placements`, `ad_campaign_placements`, `ad_impressions`, `ad_clicks` |
| 016 | `016_api_clients_webhooks.php` | API Tierces | `api_clients`, `api_client_permissions`, `api_webhooks`, `api_webhook_logs` |
| 017 | `017_i18n.php` | Internationalisation | `languages`, `translations` |
| 018 | `018_seed_data.php` | Données de base | Rôles, permissions, catégories, langues, placements, etc. |
| 019 | `019_add_multi_auth_fields.php` | Multi-Auth | Ajoute `username` et `unique_id` à `users` |

## Fonctionnement interne

- Les migrations exécutées sont enregistrées dans la table `migrations` avec un numéro de batch.
- Chaque appel à `php bin/migrate.php` crée un nouveau batch.
- `down` annule les migrations par batch (les plus récentes d'abord).
- `FOREIGN_KEY_CHECKS` est désactivé pendant l'exécution pour éviter les problèmes d'ordre.

## Bonnes pratiques

1. **Ne jamais modifier** une migration déjà exécutée en production.
2. **Créer une nouvelle migration** pour chaque modification de schéma.
3. **Toujours écrire** la méthode `down()` pour permettre le rollback.
4. **Tester** avec `migrate.php fresh` avant de pousser.
5. **Nommer clairement** : `php bin/migrate.php create add_column_phone_to_merchants`.
