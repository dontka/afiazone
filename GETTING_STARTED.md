# 🚀 Guide de Démarrage — AfiaZone Architecture MVC

## Configuration Initiale

### 1. Cloner le .env
```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Ou Linux/Mac
cp .env.example .env
```

### 2. Générer la clé d'application

Votre clé APP_KEY générée :
```
APP_KEY=base64:YfZStbkopE0AEsw9HP7VCbOB+bA0DtpLoRbLVWimwCI=
```

**Ensuite** :
1. Ouvrez le fichier `.env` (créé à l'étape 1)
2. Remplacez la ligne :
   ```
   APP_KEY=base64:GENERATED_KEY_HERE
   ```
   Par :
   ```
   APP_KEY=base64:YfZStbkopE0AEsw9HP7VCbOB+bA0DtpLoRbLVWimwCI=
   ```
3. Sauvegardez le fichier

**Ou avec PowerShell** (automatisé) :
```powershell
(Get-Content .env) -replace 'APP_KEY=.*', 'APP_KEY=base64:YfZStbkopE0AEsw9HP7VCbOB+bA0DtpLoRbLVWimwCI=' | Set-Content .env
```

### 3. Installer les dépendances Composer

Installez les dépendances PHP requises :

```bash
composer install
```

Cela créera le dossier `vendor/` avec toutes les dépendances nécessaires. L'application utilise le file-based caching par défaut (stocké dans `storage/cache/`).

### 4. Initialiser la base de données

Deux options : **migrations** (recommandé) ou import rapide.

#### Option A — Migrations (recommandé)
```bash
# Exécuter toutes les migrations (crée les tables étape par étape)
php bin/migrate.php
```

#### Option B — Import direct du schéma complet
```bash
php bin/setup-db.php
```

> Voir [docs/MIGRATIONS.md](docs/MIGRATIONS.md) pour la documentation complète des migrations.

### 5. Initialiser les rôles et permissions

```bash
# Rôles, permissions et mappings par défaut
# (déjà inclus dans la migration 018_seed_data si vous avez fait l'Option A)

# Créer un utilisateur admin :
php bin/seed-roles.php --with-admin admin@afiazone.com motdepasse
```

### 6. Vérifier l'installation

```bash
# Vérifier le statut des migrations
php bin/migrate.php status

# Lancer les tests d'authentification
php tests/test-auth.php
```

### 7. Accéder au site
```bash
# Avec Laragon (Windows)
# Double-cliquez sur "afiazone" dans Laragon ou ouvrez
http://afiazone.test/
http://localhost/afiazone/

# Le serveur Apache/PHP est automatiquement lancé par Laragon
```

---

## Commandes utiles

| Commande | Description |
|---|---|
| `php bin/migrate.php` | Exécuter les migrations en attente |
| `php bin/migrate.php status` | Voir le statut de chaque migration |
| `php bin/migrate.php down` | Annuler la dernière migration |
| `php bin/migrate.php down 3` | Annuler les 3 derniers lots |
| `php bin/migrate.php reset` | Annuler TOUTES les migrations |
| `php bin/migrate.php fresh` | Drop tout + re-migrer |
| `php bin/migrate.php create nom` | Créer une nouvelle migration |
| `php bin/setup-db.php` | Import direct du schema.sql |
| `php bin/seed-roles.php` | Seeder rôles & permissions |
| `php tests/test-auth.php` | Tests d'authentification |

## Structure de la base de données

La base comporte **74 tables** réparties en 18 migrations modulaires :

| Migration | Module | Tables |
|---|---|---|
| 001 | Users & Auth | users, roles, user_roles, permissions, role_permissions, tokens |
| 002 | Profiles & KYC | user_profiles, kyc_submissions, kyc_documents |
| 003 | Merchants | merchant_tiers, merchants, merchant_shipping_info, merchant_fees |
| 004 | Products | product_categories, products, product_images, product_attributes, product_variants, merchant_stocks |
| 005 | Orders & Cart | shopping_carts, shopping_cart_items, orders, order_items, order_status_logs, delivery_addresses |
| 006 | Delivery | delivery_providers, delivery_personnel, shipments, shipment_tracking_logs |
| 007 | Wallet | wallets, wallet_transactions, wallet_balance_history, wallet_topups, wallet_reservations |
| 008 | Medical | prescriptions, prescription_verification_logs, medical_records, medical_record_access, consultations |
| 009 | Payments | user_payment_methods, payment_transactions, payment_reconciliations, refunds |
| 010 | Reviews | product_reviews, merchant_reviews, delivery_reviews |
| 011 | Notifications | notifications, reports, support_tickets, support_messages |
| 012 | Analytics | analytics_events, audit_logs, api_logs |
| 013 | Promotions | promotion_codes, promotion_code_usages, partnerships, insurance_plans, insurance_subscriptions |
| 014 | Blog | blog_categories, blog_posts, blog_tags, blog_post_tags, blog_comments |
| 015 | Advertising | ad_campaigns, ad_placements, ad_campaign_placements, ad_impressions, ad_clicks |
| 016 | API Clients | api_clients, api_client_permissions, api_webhooks, api_webhook_logs |
| 017 | i18n | languages, translations |
| 018 | Seed Data | Rôles, permissions, catégories, langues, placements pub, catégories blog |