# 📋 Plan de Développement Complet — AfiaZone

## Medical Marketplace avec E-Wallet Santé

**Version** : 1.0  
**Date** : Mars 2026  
**Durée estimée** : 22–28 semaines  
**Architecture** : PHP 8.1+ (MVC personnalisé) + MySQL 8 + File-based Caching

---

## 📑 Table des matières

1. [Phase A – Préparation & Analyse](#phase-a--préparation--analyse)
2. [Phase B – Architecture & Base de données](#phase-b--architecture--base-de-données)
3. [Phase C – Infrastructure & Boilerplate](#phase-c--infrastructure--boilerplate)
4. [Phase D – Authentification & Autorisations](#phase-d--authentification--autorisations)
5. [Phase E – Module Utilisateurs & KYC](#phase-e--module-utilisateurs--kyc)
6. [Phase F – Catalogue Produits](#phase-f--catalogue-produits)
7. [Phase G – Panier & Commandes](#phase-g--panier--commandes)
8. [Phase H – Livraison & Livreurs](#phase-h--livraison--livreurs)
9. [Phase I – Wallet Santé](#phase-i--wallet-santé)
10. [Phase J – Ordonnances & Dossier Médical](#phase-j--ordonnances--dossier-médical)
11. [Phase K – Paiements & Mobile Money](#phase-k--paiements--mobile-money)
12. [Phase L – Administration & Modération](#phase-l--administration--modération)
13. [Phase M – Statistiques & Analytics](#phase-m--statistiques--analytics)
14. [Phase N – Notifications & Emails](#phase-n--notifications--emails)
15. [Phase O – Sécurité & Conformité](#phase-o--sécurité--conformité)
16. [Phase P – Tests & QA](#phase-p--tests--qa)
17. [Phase Q – Déploiement & Monitoring](#phase-q--déploiement--monitoring)
18. [Phase R – Post-lancement & Optimisation](#phase-r--post-lancement--optimisation)
19. [Phase S – Blog & Gestion de Contenu](#phase-s--blog--gestion-de-contenu)
20. [Phase T – Publicité In-App](#phase-t--publicité-in-app)
21. [Phase U – API Tierces Parties](#phase-u--api-tierces-parties)
22. [Phase V – Internationalisation (i18n)](#phase-v--internationalisation-i18n)

---

# PHASE A – Préparation & Analyse 

**Durée estimée** : 1–2 semaines

## A.1 Documentation & Spécifications (Statut: Complété)

### A.1.1 – Affiner les spécifications fonctionnelles 

- [ ] Réunions avec stakeholders pour clarifier les besoins exacts
- [ ] Créer des user stories pour chaque fonctionnalité majeure
- [ ] Définir les flux utilisateur (wireframes/mockups)
- [ ] Documenter les règles de gestion métier (commissions, frais, etc.)
- [ ] Valider les régulations locales (RDC) concernant :
  - Pharmaceutique (liste des médicaments autorisés)
  - FinTech / e-wallet
  - Protection des données (confidentialité dossiers médicaux)

### A.1.2 – Définir les personas

- [ ] Admin de plateforme
- [ ] Marchand (Grossiste, Producteur, Détaillant)
- [ ] Client (Patient)
- [ ] Livreur
- [ ] Partenaire (Assureur, Mutuelle)

### A.1.3 – Priorisation des features

- [ ] MVP (Minimum Viable Product)
  - Marketplace médicale basique
  - Inscription/Login
  - Catalogue produits
  - Panier & commande simple
  - Paiement basique
- [ ] Phase 2 (Wallet & features avancées)
- [ ] Backlog futur

## A.2 Environnement & Stack technique

### A.2.1 – Fixer le stack technique

- [ ] **Backend** : PHP 8.1+, pas de framework (MVC custom)
- [ ] **Base de données** : MySQL 8.0+
- [ ] **Cache & Queue** : File-based storage (scalable)
- [ ] **Stockage fichiers** : S3 compatible (ou dossier local `uploads/`)
- [ ] **CI/CD** : GitHub Actions ou GitLab CI
- [ ] **Hébergement** : VPS ou Shared Hosting avec PHP 8.1+
- [ ] **Monitoring** : Logs fichier, Sentry (optionnel)

### A.2.2 – Mettre en place le repository Git

- [ ] Créer structure de branches (main, develop, feature/\*)
- [ ] .gitignore approprié (vendor/, uploads/, .env, etc.)
- [ ] Document CONTRIBUTING.md
- [ ] README.md avec setup local

### A.2.3 – Préparation de l'environnement de développement

- [ ] Setup local : Laragon ou XAMPP (PHP 8.1+, MySQL)
- [ ] Installation Composer & dépendances initiales
- [ ] Configuration virtualhost (http://afiazone.local)
- [ ] Exécution script `bin/setup-db.php` pour initialiser la BDD
- [ ] PHP coding standards (PSR-12)
- [ ] Linter & formatter (PHP CS Fixer)

---

# PHASE B – Architecture & Base de données

**Durée estimée** : 1 semaine

## B.1 Architecture applicative

### B.1.1 – Définir la structure MVC

```
index.php                    # POINT D'ENTRÉE PRINCIPAL (router)

/app
  helpers.php                # Fonctions d'aide globales
  /Controllers
    /ProductController.php
    /OrderController.php
    /WalletController.php
    /UserController.php
    ... etc
  /Models
    /User.php
    /Product.php
    /Order.php
    ... etc
  /Services
    /WalletService.php
    /PaymentService.php
    /KycService.php
    ... etc
  /Repositories             # Optionnel
  /Middleware
  /Validators
  /Exceptions
  /Console                   # CLI commands

/routes
  api.php                   # Routes API (v1, v2, ...)
  web.php                   # Routes web (optionnel)

/config
  database.php
  app.php
  services.php

/html                       # TEMPLATES & PAGES
  /back                     # Admin dashboards, gestion
  /front                    # Pages clients
  /...pages HTML

/assets                     # ASSETS PUBLIC
  /css                      # Feuilles de style
  /js                       # Scripts JavaScript
  /img                      # Images (avatars, backgrounds, produits)
  /json                     # Données JSON
  /audio, /svg              # Médias additionnels

/js                         # Scripts globaux du site
  bootstrap.js
  menu.js
  ... etc

/fonts                      # Polices & icônes custom
```

### B.1.2 – Design Pattern MVC

- [ ] Pattern Repository pour accès données (optionnel)
- [ ] Service Layer pour logique métier centralisée
- [ ] DTO (Data Transfer Objects) pour communication API
- [ ] Exception custom organisées par domaine
- [ ] Models avec accès données intégré (ActiveRecord-like)
- [ ] Controllers légers déléguant métier aux Services

### B.1.3 – Middleware & Security

- [ ] CORS middleware (si API)
- [ ] Authentication middleware (JWT ou Session PHP native)
- [ ] RBAC (Role-Based Access Control) middleware
- [ ] Rate limiting middleware (optionnel)
- [ ] CSRF token validation
- [ ] Logging middleware (audit trail)
- [ ] Exception handler centralisé

## B.2 Schéma de base de données complet

### B.2.1 – Tables cœur (Utilisateurs & Authentification)

```sql
-- Utilisateurs
CREATE TABLE users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(20) UNIQUE,
  password_hash VARCHAR(255),
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  status ENUM('active','inactive','banned','pending_verification') DEFAULT 'pending_verification',
  email_verified_at DATETIME,
  phone_verified_at DATETIME,
  last_login_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_phone (phone),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Rôles
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Associations utilisateur-rôles
CREATE TABLE user_roles (
  user_id BIGINT,
  role_id INT,
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Permissions
CREATE TABLE permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Associations rôle-permissions
CREATE TABLE role_permissions (
  role_id INT,
  permission_id INT,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Profils utilisateur détendus
CREATE TABLE user_profiles (
  user_id BIGINT PRIMARY KEY,
  bio TEXT,
  avatar_url VARCHAR(512),
  phone_number VARCHAR(20),
  country VARCHAR(100),
  city VARCHAR(100),
  address VARCHAR(512),
  postal_code VARCHAR(20),
  company_name VARCHAR(255),
  company_type VARCHAR(100), -- 'individual', 'enterprise', etc.
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Tokens (JWT, verification, reset)
CREATE TABLE tokens (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  token_type ENUM('email_verification','password_reset','jwt','api','two_factor') DEFAULT 'api',
  token_hash VARCHAR(255) UNIQUE NOT NULL,
  expires_at DATETIME,
  is_used BOOLEAN DEFAULT FALSE,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token_hash (token_hash),
  INDEX idx_expires_at (expires_at),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.2 – Tables KYC & Modération

```sql
-- KYC (Know Your Customer)
CREATE TABLE kyc_submissions (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Documents KYC
CREATE TABLE kyc_documents (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Niveaux marchands
CREATE TABLE merchant_tiers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name ENUM('verified','premium','gold','diamond') UNIQUE,
  display_name VARCHAR(50),
  requirements_json JSON, -- conditions d'accès
  sales_commission_percent DECIMAL(5,2),
  advertisement_fee DECIMAL(10,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.3 – Tables Marchands

```sql
-- Marchands
CREATE TABLE merchants (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNIQUE NOT NULL,
  business_name VARCHAR(255),
  business_type ENUM('wholesaler','producer','retailer') DEFAULT 'retailer',
  tier_id INT DEFAULT 1, -- Verified par défaut
  description TEXT,
  logo_url VARCHAR(512),
  cover_image_url VARCHAR(512),
  rating DECIMAL(3,2) DEFAULT 0,
  total_reviews INT DEFAULT 0,
  total_sales DECIMAL(14,2) DEFAULT 0,
  status ENUM('active','suspended','banned') DEFAULT 'active',
  verification_date DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (tier_id) REFERENCES merchant_tiers(id),
  INDEX idx_status (status),
  INDEX idx_tier_id (tier_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Détails de livraison du marchand
CREATE TABLE merchant_shipping_info (
  merchant_id BIGINT PRIMARY KEY,
  warehouse_address VARCHAR(512),
  warehouse_city VARCHAR(100),
  warehouse_country VARCHAR(100),
  return_policy TEXT,
  processing_time_days INT,
  accepts_cash_on_delivery BOOLEAN DEFAULT TRUE,
  accepts_wallet_payment BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Paramètres de frais du marchand
CREATE TABLE merchant_fees (
  merchant_id BIGINT PRIMARY KEY,
  commission_percent DECIMAL(5,2),
  return_fee_percent DECIMAL(5,2),
  refund_processing_days INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.4 – Tables Catalogue Produits

```sql
-- Catégories de produits
CREATE TABLE product_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT,
  name VARCHAR(255) UNIQUE NOT NULL,
  slug VARCHAR(255) UNIQUE,
  description TEXT,
  icon_url VARCHAR(512),
  is_active BOOLEAN DEFAULT TRUE,
  display_order INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL,
  INDEX idx_slug (slug),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Produits
CREATE TABLE products (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  sku VARCHAR(100) NOT NULL,
  name VARCHAR(512) NOT NULL,
  slug VARCHAR(512) UNIQUE,
  description LONGTEXT,
  category_id INT,
  subcategory_id INT,
  price DECIMAL(12,2) NOT NULL,
  cost_price DECIMAL(12,2),
  tax_rate DECIMAL(5,2) DEFAULT 0,
  prescription_required BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  is_featured BOOLEAN DEFAULT FALSE,
  rating DECIMAL(3,2) DEFAULT 0,
  review_count INT DEFAULT 0,
  status ENUM('draft','published','archived','pending_review') DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES product_categories(id),
  FOREIGN KEY (subcategory_id) REFERENCES product_categories(id),
  INDEX idx_merchant_id (merchant_id),
  INDEX idx_category_id (category_id),
  INDEX idx_is_active (is_active),
  INDEX idx_slug (slug),
  FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Images produits
CREATE TABLE product_images (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  image_url VARCHAR(512),
  alt_text VARCHAR(255),
  is_primary BOOLEAN DEFAULT FALSE,
  display_order INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_id (product_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Attributs produits (couleur, taille, dosage, etc.)
CREATE TABLE product_attributes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  attribute_name VARCHAR(100),
  attribute_value VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Variants produits (ex: taille S, M, L)
CREATE TABLE product_variants (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  sku_suffix VARCHAR(50),
  variant_name VARCHAR(255),
  variant_price DECIMAL(12,2),
  stock_quantity INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Stock du marchand
CREATE TABLE merchant_stocks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  variant_id BIGINT,
  quantity INT DEFAULT 0,
  reorder_level INT DEFAULT 10,
  last_restock_date DATETIME,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_stock (merchant_id, product_id, variant_id),
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
  INDEX idx_merchant_id (merchant_id),
  INDEX idx_product_id (product_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.5 – Tables Panier & Commandes

```sql
-- Paniers
CREATE TABLE shopping_carts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  session_id VARCHAR(255),
  total_price DECIMAL(12,2) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_session_id (session_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Articles du panier
CREATE TABLE shopping_cart_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  cart_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  variant_id BIGINT,
  merchant_id BIGINT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price_at_add DECIMAL(12,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cart_id) REFERENCES shopping_carts(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (variant_id) REFERENCES product_variants(id),
  FOREIGN KEY (merchant_id) REFERENCES merchants(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Commandes
CREATE TABLE orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  user_id BIGINT NOT NULL,
  total_amount DECIMAL(14,2),
  tax_amount DECIMAL(12,2) DEFAULT 0,
  shipping_fee DECIMAL(12,2) DEFAULT 0,
  discount_amount DECIMAL(12,2) DEFAULT 0,
  final_amount DECIMAL(14,2),
  payment_method ENUM('cash_on_delivery','wallet','card','mobile_money') DEFAULT 'cash_on_delivery',
  payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  delivery_type ENUM('home_delivery','pickup') DEFAULT 'home_delivery',
  delivery_date DATETIME,
  requires_prescription BOOLEAN DEFAULT FALSE,
  prescription_verified BOOLEAN DEFAULT FALSE,
  prescription_verified_by BIGINT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (prescription_verified_by) REFERENCES users(id),
  INDEX idx_user_id (user_id),
  INDEX idx_order_status (order_status),
  INDEX idx_payment_status (payment_status),
  INDEX idx_created_at (created_at),
  INDEX idx_order_number (order_number)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Articles de commande
CREATE TABLE order_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  variant_id BIGINT,
  merchant_id BIGINT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2),
  tax_amount DECIMAL(12,2) DEFAULT 0,
  subtotal DECIMAL(14,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (variant_id) REFERENCES product_variants(id),
  FOREIGN KEY (merchant_id) REFERENCES merchants(id),
  INDEX idx_order_id (order_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Historique des statuts de commande
CREATE TABLE order_status_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  previous_status VARCHAR(50),
  new_status VARCHAR(50),
  changed_by BIGINT,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES users(id),
  INDEX idx_order_id (order_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Adresses de livraison
CREATE TABLE delivery_addresses (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  recipient_name VARCHAR(255),
  phone_number VARCHAR(20),
  street_address VARCHAR(512),
  city VARCHAR(100),
  state_or_region VARCHAR(100),
  postal_code VARCHAR(20),
  country VARCHAR(100),
  is_default BOOLEAN DEFAULT FALSE,
  latitude DECIMAL(10,8),
  longitude DECIMAL(11,8),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.6 – Tables Livraison

```sql
-- Fournisseurs/Partenaires de livraison
CREATE TABLE delivery_providers (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Livreurs
CREATE TABLE delivery_personnel (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Livraisons
CREATE TABLE shipments (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Historique des mises à jour de livraison
CREATE TABLE shipment_tracking_logs (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.7 – Tables Wallet & Transactions

```sql
-- Portefeuilles (Wallets)
CREATE TABLE wallets (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNIQUE NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  balance DECIMAL(14,2) DEFAULT 0,
  reserved_balance DECIMAL(14,2) DEFAULT 0, -- montants réservés (pas encore dépensés)
  available_balance DECIMAL(14,2) DEFAULT 0, -- solde disponible
  total_received DECIMAL(14,2) DEFAULT 0,
  total_spent DECIMAL(14,2) DEFAULT 0,
  status ENUM('active','frozen','suspended') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Transactions du wallet (double-entry bookkeeping)
CREATE TABLE wallet_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  transaction_type ENUM('credit','debit','reserve','release') DEFAULT 'debit',
  amount DECIMAL(14,2) NOT NULL,
  balance_before DECIMAL(14,2),
  balance_after DECIMAL(14,2),
  external_reference VARCHAR(255), -- ID du système de paiement externe
  payment_method ENUM('wallet','card','mobile_money','bank_transfer','cash','bonus') DEFAULT 'wallet',
  description TEXT,
  metadata JSON, -- données additionnelles
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_status (status),
  INDEX idx_external_reference (external_reference),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Historique des soldes (audit trail)
CREATE TABLE wallet_balance_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  balance DECIMAL(14,2),
  reserved_balance DECIMAL(14,2),
  available_balance DECIMAL(14,2),
  snapshot_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_snapshot_date (snapshot_date)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- TOP-UP (dépôts dans le wallet)
CREATE TABLE wallet_topups (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  amount DECIMAL(14,2),
  payment_method ENUM('card','mobile_money','bank_transfer') DEFAULT 'mobile_money',
  external_transaction_id VARCHAR(255),
  status ENUM('pending','completed','failed') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Réservations de fonds (réservation au moment de la commande)
CREATE TABLE wallet_reservations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  order_id BIGINT,
  amount DECIMAL(14,2),
  reason ENUM('order_payment','merchant_settlement','fee','refund') DEFAULT 'order_payment',
  status ENUM('reserved','released','consumed') DEFAULT 'reserved',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  released_at DATETIME,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.8 – Tables Prescription & Dossier Médical

```sql
-- Ordonnances médicales
CREATE TABLE prescriptions (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Historique des vérifications de prescription
CREATE TABLE prescription_verification_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  prescription_id BIGINT NOT NULL,
  verified_by BIGINT,
  status VARCHAR(50),
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Dossier médical électronique (EMR)
CREATE TABLE medical_records (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  record_type ENUM('diagnosis','treatment','lab_result','vaccination','consultation','surgery','allergy','medication') DEFAULT 'consultation',
  title VARCHAR(255),
  description LONGTEXT,
  provider_name VARCHAR(255),
  provider_facility VARCHAR(255),
  recorded_date DATETIME,
  file_url VARCHAR(512), -- PDF, image, etc.
  is_shared_with_all_providers BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_record_type (record_type)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Partage des dossiers médicaux (access control)
CREATE TABLE medical_record_access (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  medical_record_id BIGINT NOT NULL,
  authorized_user_id BIGINT NOT NULL,
  access_type ENUM('view','view_edit') DEFAULT 'view',
  expires_at DATETIME,
  granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
  FOREIGN KEY (authorized_user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_access (medical_record_id, authorized_user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Consultations médicales
CREATE TABLE consultations (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.9 – Tables Paiements

```sql
-- Méthodes de paiement de l'utilisateur
CREATE TABLE user_payment_methods (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  payment_method_type ENUM('card','mobile_money','bank_account','wallet') DEFAULT 'card',
  name VARCHAR(100), -- étiquette personnalisée
  gateway_id VARCHAR(255), -- Stripe token, etc.
  last_four VARCHAR(4), -- 4 derniers chiffres
  is_default BOOLEAN DEFAULT FALSE,
  is_verified BOOLEAN DEFAULT FALSE,
  expires_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Transactions de paiement
CREATE TABLE payment_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  amount DECIMAL(14,2),
  currency VARCHAR(3) DEFAULT 'USD',
  payment_method ENUM('wallet','card','mobile_money','cash_on_delivery') DEFAULT 'wallet',
  payment_gateway VARCHAR(50), -- Stripe, Flutterwave, Orange Money API, etc.
  gateway_transaction_id VARCHAR(255),
  status ENUM('initiated','processing','completed','failed','refunded','cancelled') DEFAULT 'initiated',
  payment_date DATETIME,
  completion_date DATETIME,
  failure_reason TEXT,
  metadata JSON,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_order_id (order_id),
  INDEX idx_status (status),
  INDEX idx_gateway_transaction_id (gateway_transaction_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Réconciliations de paiement
CREATE TABLE payment_reconciliations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  payment_transaction_id BIGINT NOT NULL,
  gateway_receipt_reference VARCHAR(255),
  settlement_status ENUM('pending','settled','failed','disputed') DEFAULT 'pending',
  settled_amount DECIMAL(14,2),
  settled_date DATETIME,
  fees DECIMAL(10,2),
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id) ON DELETE CASCADE,
  INDEX idx_payment_transaction_id (payment_transaction_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Remboursements
CREATE TABLE refunds (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  payment_transaction_id BIGINT,
  amount DECIMAL(14,2),
  reason ENUM('customer_request','payment_failed','item_unavailable','damaged_item','prescription_rejected','order_cancelled') DEFAULT 'customer_request',
  status ENUM('pending','processing','completed','failed','rejected') DEFAULT 'pending',
  processed_by BIGINT,
  processed_date DATETIME,
  refund_method ENUM('original_method','wallet','bank_transfer') DEFAULT 'original_method',
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id),
  FOREIGN KEY (processed_by) REFERENCES users(id),
  INDEX idx_order_id (order_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.10 – Tables Avis & Notations

```sql
-- Avis produits
CREATE TABLE product_reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  order_id BIGINT,
  rating INT DEFAULT 5, -- 1-5
  title VARCHAR(255),
  comment TEXT,
  is_verified_purchase BOOLEAN DEFAULT TRUE,
  helpful_count INT DEFAULT 0,
  status ENUM('pending','approved','rejected','flagged') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  INDEX idx_product_id (product_id),
  INDEX idx_rating (rating)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Avis marchands
CREATE TABLE merchant_reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  order_id BIGINT,
  rating INT DEFAULT 5,
  comment TEXT,
  is_verified_purchase BOOLEAN DEFAULT TRUE,
  service_rating INT,
  delivery_rating INT,
  packaging_rating INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  INDEX idx_merchant_id (merchant_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Avis livreurs
CREATE TABLE delivery_reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  delivery_personnel_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  shipment_id BIGINT,
  rating INT DEFAULT 5,
  comment TEXT,
  punctuality_rating INT,
  professionalism_rating INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (delivery_personnel_id) REFERENCES delivery_personnel(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE SET NULL,
  INDEX idx_delivery_personnel_id (delivery_personnel_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.11 – Tables Notifications & Support

```sql
-- Notifications
CREATE TABLE notifications (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Signalements / Modération
CREATE TABLE reports (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Support / Tickets
CREATE TABLE support_tickets (
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
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Messages de support
CREATE TABLE support_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ticket_id BIGINT NOT NULL,
  user_id BIGINT,
  message TEXT,
  attachment_url VARCHAR(512),
  is_internal BOOLEAN DEFAULT FALSE, -- visible uniquement aux admin
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.12 – Tables Analytics & Logging

```sql
-- Événements d'analyse
CREATE TABLE analytics_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  event_type ENUM('page_view','product_view','add_to_cart','remove_from_cart','checkout','payment','search','filter','click','scroll') DEFAULT 'page_view',
  event_category VARCHAR(100),
  event_label VARCHAR(255),
  event_value DECIMAL(12,2),
  page_url VARCHAR(512),
  referrer_url VARCHAR(512),
  ip_address VARCHAR(45),
  user_agent TEXT,
  session_id VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_event_type (event_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Logs d'audit
CREATE TABLE audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  action VARCHAR(255),
  entity_type VARCHAR(100),
  entity_id BIGINT,
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_entity_type (entity_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Logs API
CREATE TABLE api_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  endpoint VARCHAR(512),
  method VARCHAR(10),
  user_id BIGINT,
  status_code INT,
  response_time_ms INT,
  request_ip VARCHAR(45),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_endpoint (endpoint),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.13 – Tables Promotions & Partenariats

```sql
-- Codes de promotion
CREATE TABLE promotion_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  discount_type ENUM('percentage','fixed_amount') DEFAULT 'percentage',
  discount_value DECIMAL(10,2),
  max_uses INT,
  current_uses INT DEFAULT 0,
  usage_per_user INT DEFAULT 1,
  min_order_amount DECIMAL(12,2),
  applicable_to ENUM('all_products','specific_category','specific_merchant') DEFAULT 'all_products',
  applicable_item_id INT,
  start_date DATETIME,
  end_date DATETIME,
  is_active BOOLEAN DEFAULT TRUE,
  created_by BIGINT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_code (code),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Utilisations de code
CREATE TABLE promotion_code_usages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  promotion_code_id INT NOT NULL,
  user_id BIGINT NOT NULL,
  order_id BIGINT,
  discount_amount DECIMAL(12,2),
  used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (promotion_code_id) REFERENCES promotion_codes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Partenariats
CREATE TABLE partnerships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partner_name VARCHAR(255),
  partner_type ENUM('insurance','mutual','pharmacy','clinic','laboratory') DEFAULT 'insurance',
  contact_person VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(20),
  is_active BOOLEAN DEFAULT TRUE,
  agreement_url VARCHAR(512),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Plans d'assurance / Mutuelles
CREATE TABLE insurance_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partnership_id INT,
  plan_name VARCHAR(255),
  description TEXT,
  monthly_premium DECIMAL(10,2),
  coverage_percentage DECIMAL(5,2),
  max_coverage_amount DECIMAL(14,2),
  is_active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partnership_id) REFERENCES partnerships(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Adhésions aux assurances
CREATE TABLE insurance_subscriptions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  insurance_plan_id INT,
  status ENUM('active','cancelled','expired','suspended') DEFAULT 'active',
  start_date DATE,
  end_date DATE,
  auto_renew BOOLEAN DEFAULT TRUE,
  premium_paid_to_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plans(id),
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.14 – Tables Blog & Contenu

```sql
-- Catégories de blog
CREATE TABLE blog_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  display_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES blog_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Articles de blog
CREATE TABLE blog_posts (
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
  FULLTEXT INDEX ft_blog_search (title, content)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Tags
CREATE TABLE blog_tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Pivot post ↔ tag
CREATE TABLE blog_post_tags (
  post_id BIGINT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Commentaires
CREATE TABLE blog_comments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  parent_id BIGINT,
  content TEXT NOT NULL,
  status ENUM('pending','approved','rejected','spam') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.15 – Tables Publicité In-App

```sql
-- Campagnes publicitaires
CREATE TABLE ad_campaigns (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  campaign_type ENUM('banner','sidebar','featured_product','popup','interstitial') DEFAULT 'banner',
  target_url VARCHAR(512),
  image_url VARCHAR(512),
  content_html TEXT,
  target_category_id INT,
  target_user_type ENUM('all','customer','merchant','deliverer') DEFAULT 'all',
  daily_budget DECIMAL(10,2),
  total_budget DECIMAL(12,2),
  spent_amount DECIMAL(12,2) DEFAULT 0,
  cost_model ENUM('cpm','cpc','fixed') DEFAULT 'cpc',
  cost_per_unit DECIMAL(8,4),
  frequency_cap INT,
  status ENUM('draft','active','paused','completed','cancelled') DEFAULT 'draft',
  start_date DATETIME,
  end_date DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (target_category_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Emplacements publicitaires
CREATE TABLE ad_placements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) UNIQUE NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  dimensions VARCHAR(50),
  max_ads INT DEFAULT 1,
  is_active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Pivot campagne ↔ emplacement
CREATE TABLE ad_campaign_placements (
  campaign_id BIGINT NOT NULL,
  placement_id INT NOT NULL,
  PRIMARY KEY (campaign_id, placement_id),
  FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
  FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Impressions
CREATE TABLE ad_impressions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT NOT NULL,
  placement_id INT,
  user_id BIGINT,
  session_id VARCHAR(255),
  ip_address VARCHAR(45),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
  INDEX idx_campaign_id (campaign_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Clics
CREATE TABLE ad_clicks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT NOT NULL,
  impression_id BIGINT,
  placement_id INT,
  user_id BIGINT,
  ip_address VARCHAR(45),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
  INDEX idx_campaign_id (campaign_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.16 – Tables API Tierces Parties

```sql
-- Clients API (tiers)
CREATE TABLE api_clients (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT,
  name VARCHAR(255) NOT NULL,
  api_key VARCHAR(255) UNIQUE NOT NULL,
  api_secret_hash VARCHAR(255) NOT NULL,
  environment ENUM('sandbox','production') DEFAULT 'sandbox',
  is_active BOOLEAN DEFAULT TRUE,
  requests_per_minute INT DEFAULT 60,
  requests_per_day INT DEFAULT 10000,
  allowed_ips JSON,
  last_used_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE SET NULL,
  INDEX idx_api_key (api_key)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Permissions API par client
CREATE TABLE api_client_permissions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  api_client_id BIGINT NOT NULL,
  permission VARCHAR(100) NOT NULL, -- ex: products.read, orders.write
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE,
  UNIQUE KEY unique_client_perm (api_client_id, permission)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Webhooks sortants
CREATE TABLE api_webhooks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  api_client_id BIGINT NOT NULL,
  url VARCHAR(512) NOT NULL,
  secret_hash VARCHAR(255) NOT NULL,
  events JSON NOT NULL, -- ["order.created","payment.completed",...]
  is_active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Logs de livraison webhooks
CREATE TABLE api_webhook_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  webhook_id BIGINT NOT NULL,
  event_type VARCHAR(100) NOT NULL,
  payload JSON,
  response_status_code INT,
  response_body TEXT,
  latency_ms INT,
  attempt INT DEFAULT 1,
  status ENUM('success','failed','pending') DEFAULT 'pending',
  next_retry_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (webhook_id) REFERENCES api_webhooks(id) ON DELETE CASCADE,
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4;
```

### B.2.17 – Tables Internationalisation (i18n)

```sql
-- Langues supportées
CREATE TABLE languages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) UNIQUE NOT NULL, -- 'fr', 'en', 'sw'
  name VARCHAR(100) NOT NULL,       -- 'Français', 'Anglais', 'Swahili'
  native_name VARCHAR(100) NOT NULL,-- 'Français', 'English', 'Kiswahili'
  flag_icon VARCHAR(50),
  is_default BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  is_rtl BOOLEAN DEFAULT FALSE,
  display_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_code (code)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Traductions (clés hiérarchiques : namespace.group.item)
CREATE TABLE translations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  locale VARCHAR(10) NOT NULL,          -- 'fr', 'en', 'sw'
  namespace VARCHAR(100) NOT NULL DEFAULT 'general',
  group_key VARCHAR(100) NOT NULL,      -- 'auth', 'product', 'order'
  item_key VARCHAR(255) NOT NULL,       -- 'login.title', 'add_to_cart'
  value TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_translation (locale, namespace, group_key, item_key),
  INDEX idx_locale (locale),
  INDEX idx_namespace_group (namespace, group_key)
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Ajout préférence langue au profil utilisateur
ALTER TABLE user_profiles ADD COLUMN preferred_locale VARCHAR(10) DEFAULT 'fr';
```

### B.2.18 – Créer les indices importants

```sql
-- Indices de performance critiques
CREATE INDEX idx_orders_user_date ON orders(user_id, created_at DESC);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_wallet_trans_date ON wallet_transactions(wallet_id, created_at DESC);
CREATE INDEX idx_products_category_active ON products(category_id, is_active);
CREATE INDEX idx_merchant_stocks_availability ON merchant_stocks(merchant_id, quantity);
CREATE INDEX idx_prescription_status_order ON prescriptions(order_id, verification_status);
CREATE INDEX idx_shipment_status_date ON shipments(status, created_at DESC);
```

---

## B.3 Modèle d'authentification

### B.3.1 – JWT vs Sessions

- [ ] Décider entre JWT (stateless, API-friendly) ou Sessions (PHP traditionnel)
- [ ] **Recommandation MVP** : Sessions + tokens de rafraîchissement pour API
- [ ] Définir durées d'expiration (1h pour access token, 7j pour refresh token)

### B.3.2 – 2FA (optionnel pour MVP)

- [ ] Implémentation TOTP (Time-based One-Time Password) via Google Authenticator
- [ ] SMS OTP comme option secondaire (si coût acceptable)

---

# PHASE C – Infrastructure & Boilerplate

**Durée estimée** : 2–3 jours

## C.1 Setup de l'environnement de développement

### C.1.1 – Installation Laragon (ou XAMPP)

- [ ] Télécharger et installer Laragon (PHP 8.1+, MySQL)
- [ ] Configuration virtualhost `afiazone.test`
- [ ] Configurer PHP : `upload_max_filesize`, `memory_limit`, `display_errors = on`
- [ ] Extensions activées : PDO, cURL, OpenSSL, JSON, GD, Mbstring
- [ ] Test : `php -v` et accès à http://afiazone.test

### C.1.2 – Configuration du serveur local

- [ ] Mise en place du `.env` (copier de `.env.example`)
- [ ] Configuration database : host, user, password, database
- [ ] Configuration cache (file-based par défaut)
- [ ] Configuration upload_dir : `uploads/`

### C.1.3 – Initialisation de la base de données

- [ ] Créer base de données MySQL `afiazone`
- [ ] Exécuter `php bin/setup-db.php` pour importer schema.sql
- [ ] Vérifier tables et indexes créés
- [ ] Seeding données initiales (rôles, catégories)

## C.2 Scaffolding MVC & Bootstrap

### C.2.1 – Structure de répertoires (vérification)

- [ ] Vérifier structure : `app/`, `config/`, `routes/`, `html/`, `assets/`, `database/`, `logs/`, `uploads/`
- [ ] Vérifier `index.php` à la racine
- [ ] Créer fichier `.env` template avec variables essentielles

### C.2.2 – Classe Router

- [ ] Implémenter Router simple (RESTful)
- [ ] Gestion des paramètres GET, POST, URI
- [ ] Middleware support et pipeline
- [ ] Dispatching vers Controllers

### C.2.3 – Classe Database

- [ ] Wrapper PDO pour connexion MySQL
- [ ] Vérifier dossiers storage/ crées (cache, logs, sessions, uploads)
- [ ] Methods CRUD basiques (select, insert, update, delete)
- [ ] Query builder simple (si souhaité)

### C.2.4 – Classe Model (BaseModel)

- [ ] BaseModel pour ORM-like functionality
- [ ] Gestion des attributes
- [ ] Méthodes CRUD : find(), all(), create(), update(), delete()
- [ ] Relations basiques : belongsTo(), hasMany()
- [ ] Timestamps : created_at, updated_at auto

### C.2.5 – Classe Controller (BaseController)

- [ ] BaseController
- [ ] Helper pour JSON responses
- [ ] Gestion des statuts HTTP
- [ ] Error handling & exceptions

### C.2.6 – Fichier bootstrap (index.php)

- [ ] Charger l'autoloader Composer
- [ ] Initialiser configuration (.env)
- [ ] Initialiser DB & connections
- [ ] Initialiser logs / error handlers
- [ ] Instancier Router et dispatcher requête

## C.3 Composer & Dépendances

### C.3.1 – Packages essentiels

- [ ] `monolog/monolog` – logging
- [ ] `phpunit/phpunit` – tests
- [ ] Cache drivers configurés (file, memcached)
- [ ] `firebase/php-jwt` – JWT (optionnel)
- [ ] `aws/aws-sdk-php` – S3 uploads
- [ ] `guzzlehttp/guzzle` – HTTP client (Mobile Money APIs)

### C.3.2 – Générer autoloader

- [ ] `composer install` PSR-4 autoloading

## C.4 Configuration & Environment

### C.4.1 – Fichier .env

```
APP_NAME=AfiaZone
APP_ENV=development
APP_DEBUG=true
APP_URL=http://afiazone.test
APP_KEY=your-secret-key

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=afiazone
DB_USERNAME=root
DB_PASSWORD=secret

JWT_SECRET=your-jwt-secret
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600
JWT_REFRESH_EXPIRY=604800

S3_BUCKET=afiazone
S3_REGION=us-east-1
S3_KEY=xxx
S3_SECRET=xxx

MAIL_DRIVER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=

# Mobile Money / Payment APIs
ORANGE_MONEY_API_KEY=xxx
MTN_MOMO_API_KEY=xxx
STRIPE_API_KEY=xxx (si applicable)
```

### C.4.2 – Classe Config

- [ ] Logger ini ou JSON avec Monolog
- [ ] Paramètres globaux applicables

---

# PHASE D – Authentification & Autorisations

**Durée estimée** : 1–2 semaines

## D.1 Module Authentication

### D.1.1 – User Registration

- [ ] Endpoint POST `/api/auth/register`
  - Validation email (unique, format valide)
  - Validation password (force, confirmation)
  - Validation phone (De preférence)
  - Hash password via bcrypt

- [ ] Envoi email de vérification (lien + token)
- [ ] Enregistrement utilisateur dans `users` table
- [ ] Status initial `pending_verification`

### D.1.2 – Email Verification

- [ ] Endpoint GET `/api/auth/verify-email?token=xxx`
- [ ] Validation token + expiry
- [ ] Update `users.email_verified_at`
- [ ] Update status → `active`
- [ ] Suppression token

### D.1.3 – User Login

- [ ] Endpoint POST `/api/auth/login`
  - Validation email/password
  - Vérifier user active + email verified
  - Générer JWT (access + refresh token) ou session
  - Log `last_login_at`
  - Retourner token + user data

### D.1.4 – Password Reset

- [ ] Endpoint POST `/api/auth/forgot-password` (envoi lien)
- [ ] Endpoint POST `/api/auth/reset-password` (reset avec token)
- [ ] Gestion expiry token (15 minutes)

### D.1.5 – Refresh Token

- [ ] Endpoint POST `/api/auth/refresh-token`
- [ ] Valider refresh token valide
- [ ] Générer nouveau access token

### D.1.6 – Logout

- [ ] Endpoint POST `/api/auth/logout`
- [ ] Si JWT : add token blacklist (file-based store)
- [ ] Si Session : destroy session

## D.2 RBAC (Role-Based Access Control)

### D.2.1 – Rôles initiaux

- [ ] Créer table `roles` avec :
  - `admin` – accès complet
  - `moderator` – gestion contenu, signalements
  - `merchant` – vente produits
  - `customer` – achat
  - `delivery_person` – livraison
  - `partner` – partenaire (assureur, financier parainnage etc.)

### D.2.2 – Permissions

- [ ] Créer table `permissions` avec actions :
  - `manage_users`
  - `manage_products`
  - `manage_orders`
  - `manage_merchants`
  - `manage_kyc`
  - `manage_support`
  - etc.

### D.2.3 – Middleware RBAC

- [ ] Classe Middleware pour valider role/permission
- [ ] Décorateurs/annotations : `@RequireRole('merchant')`, `@RequirePermission('manage_products')`
- [ ] Retourner 403 Forbidden si accès refusé

### D.2.4 – Seeders pour rôles/permissions

- [ ] Seed initial de rôles + permissions via CLI command

---

# PHASE E – Module Utilisateurs & KYC

**Durée estimée** : 1–2 semaines

## E.1 Gestion des profils utilisateurs

- [ ] Récupérer infos utilisateur
- [ ] Vérifier propriétaire ou admin
- [ ] Éditer profil (nom, adresse, etc.)
- [ ] Validation données
- [ ] Audit log

### E.1.3 – Upload avatar

- [ ] Validation format image (JPG, PNG)
- [ ] Resize + compression
- [ ] Upload S3
- [ ] Retourner URL

## E.2 Process KYC (Know Your Customer) ne concerne pas le client

### E.2.1 – Submission KYC
- [ ] Récupérer documents (ID, proof of address, etc.)
- [ ] Validation file type + size
- [ ] Upload S3
- [ ] Créer enregistrement `kyc_submissions` (status = pending)
- [ ] Notification admin

### E.2.2 – KYC Review (Admin)

- [ ] En attente, Approuvé et regété
- [ ] Audit log des décisions

### E.2.3 – Niveaux marchands

- [ ] Créer table `merchant_tiers` (verified, premium, gold, diamond)
- [ ] KYC approuvé → merchant peut être level 1 (verified)
- [ ] Critères upgrade (ventes, avis, ancienneté)

### E.2.4 – Document storage & scanning (très important)

- [ ] Intégration avec service OCR/document scanning
- [ ] Vérification automatique ID valide
- [ ] Flag documents suspects pour review humain

## E.3 Profils marchands

### E.3.1 – Merchant Registration

- [ ] Créer merchant profile
- [ ] Shipping info (warehouse address, return policy)
- [ ] Fees configuration

### E.3.2 – Merchant Dashboard

- [ ] KPIs : total sales, total orders, rating
- [ ] Récents commandes/reviews

### E.3.3 – Tier management

- [ ] Endpoint GET `/api/merchants/{id}/tier`
- [ ] Afficher conditions pour upgrade

---

# PHASE F – Catalogue Produits

**Durée estimée** : 2–3 semaines

## F.1 Gestion des catégories

### F.1.1 – CRUD Catégories

- [ ] Endpoint POST `/api/admin/categories` (créer)
- [ ] Endpoint GET `/api/categories` (liste, avec hiérarchie parent)
- [ ] Endpoint PUT `/api/admin/categories/{id}`
- [ ] Endpoint DELETE `/api/admin/categories/{id}`
- [ ] Support sous-catégories (parent_id)

## F.2 Gestion des produits (Merchant-facing)

### F.2.1 – Créer/Éditer produits

- [ ] Endpoint POST `/api/merchants/{id}/products` (créer)
- [ ] Validation : name, description, price, category
- [ ] Flag `prescription_required` si applicable
- [ ] Stock initial
- [ ] Status = `draft`

### F.2.2 – Upload images produit

- [ ] Endpoint POST `/api/products/{id}/images`
- [ ] Support multiple images
- [ ] Image resize (thumbnails)
- [ ] Upload S3
- [ ] Stocker URLs dans `product_images`

### F.2.3 – Attributes & Variants

- [ ] Endpoint POST `/api/products/{id}/variants` (créer variants)
- [ ] Pricing variants
- [ ] Stock per variant
- [ ] SKU variant

### F.2.4 – Publish workflow

- [ ] Endpoint PUT `/api/products/{id}/publish`
- [ ] Valider complétude (image, description, prix)
- [ ] Status = `published` ou `pending_review`
- [ ] Modérateur approve si `pending_review`

## F.3 Affichage catalogue (Public)

### F.3.1 – Liste produits

- [ ] Endpoint GET `/api/products` (paginated)
- [ ] Filtres : category, price range, rating, merchant
- [ ] Tri : newest, popular, cheapest, highest-rated
- [ ] Pagination (15-20 items/page)

### F.3.2 – Détail produit

- [ ] Endpoint GET `/api/products/{slug}`
- [ ] Toutes infos: Fiches médicales certifiées : posologie, contre‑indications, statut ordonnance, provenance; photos, notices PDF, et labels réglementaires.
- [ ] Merchant info
- [ ] Reviews
- [ ] Stock status

### F.3.3 – Recherche

- [ ] Endpoint GET `/api/products/search?q=xxx`
- [ ] Full-text search MySQL (`MATCH AGAINST`)
- [ ] Ou intégration Elasticsearch (optionnel)
- [ ] Recherche guidée par symptômes (avec avertissements), suggestions de produits complémentaires (panier santé), et parcours ordonnance fluide UX.

### F.3.4 – Recommendations

- [ ] Endpoint GET `/api/products/{id}/recommendations`
- [ ] Based on category, ratings, popular

## F.4 Modération produits

### F.4.1 – Admin panel

- [ ] Endpoint GET `/api/admin/products/pending`
- [ ] Endpoint GET `/api/admin/products/flagged`
- [ ] Endpoint PUT `/api/admin/products/{id}/approve`
- [ ] Endpoint DELETE `/api/admin/products/{id}` (soft delete + log)

### F.4.2 – Vérifications automatiques

- [ ] Vérifier product name vs liste médicaments autorisés (RDC)
- [ ] Flag produit si présent dans liste noire
- [ ] Queue modérateur pour review

---

# PHASE G – Panier & Commandes

**Durée estimée** : 2 semaines

## G.1 Panier (Shopping Cart)

### G.1.1 – Add to cart

- [ ] Endpoint POST `/api/cart/items`
- [ ] Validation product exists + in stock
- [ ] Create/update `shopping_carts`
- [ ] Add to `shopping_cart_items`
- [ ] Retourner cart totals

### G.1.2 – Update cart item

- [ ] Endpoint PUT `/api/cart/items/{id}`
- [ ] Modify quantity
- [ ] Re-calculate totals

### G.1.3 – Remove from cart

- [ ] Endpoint DELETE `/api/cart/items/{id}`

### G.1.4 – Get cart

- [ ] Endpoint GET `/api/cart`
- [ ] Toutes items + totals
- [ ] Discrepancies (product deleted, price changed, out of stock)

### G.1.5 – Clear cart

- [ ] Endpoint DELETE `/api/cart`

## G.2 Checkout Process

### G.2.1 – Validate cart

- [ ] Vérifier tous items en stock
- [ ] Vérifier prix actualisés
- [ ] Vérifier merchant actifs

### G.2.2 – Shipping address

- [ ] Endpoint POST `/api/checkout/address`
- [ ] Créer ou sélectionner adresse existante
- [ ] Validation compléitude (street, city, postal code)

### G.2.3 – Shipping method

- [ ] Endpoint POST `/api/checkout/shipping`
- [ ] Sélectionner home delivery ou pickup
- [ ] Calculer frais de port (basé distance, weight, merchant)
- [ ] Estimated delivery date

### G.2.4 – Payment method selection

- [ ] Endpoint POST `/api/checkout/payment-method`
- [ ] Options : wallet, card, mobile_money, cash_on_delivery
- [ ] Vérifier wallet suffisant si payment via wallet

## G.3 Création commande

### G.3.1 – Create order

- [ ] Endpoint POST `/api/orders`
- [ ] Valider cart, address, shipping, payment
- [ ] Créer `orders` record
- [ ] Créer `order_items` pour chaque item
- [ ] Générer unique order_number (timestamp + random)
- [ ] Vérifier si prescription_required → flag pour vérification
- [ ] Status = `pending`

### G.3.2 – QR Code & Delivery token

- [ ] Générer QR code (order ID + token 5-digit)
- [ ] Stocker dans `shipments`
- [ ] Inclure dans confirmation email

### G.3.3 – Inventory management

- [ ] Décrémenter stocks dans `merchant_stocks`
- [ ] Ou utiliser `wallet_reservations` pour hold inventory

## G.4 Order Management

### G.4.1 – Get order

- [ ] Endpoint GET `/api/orders/{id}`
- [ ] Vérifier ownership (user own order)
- [ ] Toutes infos + items + shipping

### G.4.2 – Order history

- [ ] Endpoint GET `/api/orders` (user's orders)
- [ ] Pagination + filtres (status, date range)

### G.4.3 – Order status updates

- [ ] Endpoint PUT `/api/orders/{id}/status`
- [ ] Admin only : confirm, process, ship, deliver, cancel
- [ ] Créer audit log `order_status_logs`
- [ ] Notification client à chaque changement

### G.4.4 – Cancel order

- [ ] Endpoint POST `/api/orders/{id}/cancel`
- [ ] Autorisé si status = pending ou confirmed
- [ ] Refund payment
- [ ] Return inventory
- [ ] Notification merchant

---

# PHASE H – Livraison & Livreurs

**Durée estimée** : 1–2 semaines

## H.1 Intégration providers livraison

### H.1.1 – Delivery providers setup

- [ ] Créer table `delivery_providers` avec API endpoints
- [ ] Intégrer avec local providers (si disponibles en RDC)
- [ ] API keys chiffrés en DB
- [ ] Fallback : in-house delivery personnel

### H.1.2 – Calculate shipping cost

- [ ] Fonction basée distance (latitude/longitude)
- [ ] Weight multiplier
- [ ] Merchant base fee
- [ ] Display au checkout

## H.2 Gestion livreurs internes

### H.2.1 – Delivery personnel registration

- [ ] Endpoint POST `/api/deliveries/personnel/register`
- [ ] Capture licence info, vehicle type, license plate
- [ ] License expiry tracking

### H.2.2 – Delivery dashboard

- [ ] Endpoint GET `/api/deliveries/available-shipments`
- [ ] List shipments assigned to current user
- [ ] Endpoint POST `/api/shipments/{id}/accept` (accept delivery)
- [ ] Endpoint PUT `/api/shipments/{id}/status`
  - picked_up
  - in_transit
  - delivered
  - failed

### H.2.3 – Live location tracking

- [ ] Endpoint POST `/api/shipments/{id}/location`
- [ ] GPS coordinates + timestamp
- [ ] Store in `shipment_tracking_logs`
- [ ] Real-time update via WebSocket (optionnel)

## H.3 Delivery verification

### H.3.1 – Signature capture

- [ ] Mobile app : signature pad
- [ ] Photo proof
- [ ] Recipient name + contact

### H.3.2 – QR code scan

- [ ] Scan QR code or enter 5-digit token
- [ ] Validate order
- [ ] Lock delivery

### H.3.3 – Update delivery status

- [ ] Mark as `delivered`
- [ ] Store signature + proof photo
- [ ] Update order status → `delivered`
- [ ] Notification client

---

# PHASE I – Wallet Santé

**Durée estimée** : 3–4 semaines

## I.1 Wallet setup

### I.1.1 – Créer wallet utilisateur

- [ ] Auto-création wallet lors registration utilisateur
- [ ] Initial balance = 0
- [ ] Direct lien user-wallet (1:1)

## I.2 Top-up / Dépôts

### I.2.1 – Initiate top-up

- [ ] Endpoint POST `/api/wallet/topup`
- [ ] Montant, method (card, mobile_money)
- [ ] Redirection vers payment gateway

### I.2.2 – Top-up webhook

- [ ] Recevoir confirmation paiement
- [ ] Créer `wallet_topups` record
- [ ] Update wallet balance
- [ ] Notification client

## I.3 Transactions wallet

### I.3.1 – Recording transactions

- [ ] Double-entry ledger in `wallet_transactions`
- [ ] transaction_type : credit (dépôt), debit (retrait), reserve (réservation), release (libération)
- [ ] Store balance_before + balance_after
- [ ] External reference pour traçabilité

### I.3.2 – Wallet balance aggregation

- [ ] Vue `available_balance` = balance - reserved_balance
- [ ] Transactions impact atomiquement
- [ ] Audit trail complet

## I.4 Payments via wallet

### I.4.1 – Payment at checkout

- [ ] Si wallet selected comme payment method
- [ ] Vérifier solde suffisant
- [ ] Réserver fonds : créer `wallet_reservations`
- [ ] Créer transaction `debit`
- [ ] Update order payment_status → `paid`

### I.4.2 – Failed payment recovery

- [ ] Si transaction fails → release reservation
- [ ] Retourner solde client

## I.5 Micro-insurance & Mutuelles

### I.5.1 – Browse insurance plans

- [ ] Endpoint GET `/api/insurance-plans`
- [ ] Afficher plans disponibles
- [ ] Premium, coverage %, max coverage amount

### I.5.2 – Subscribe to plan

- [ ] Endpoint POST `/api/insurance-subscriptions`
- [ ] Validation wallet sufficient
- [ ] Deduct premium from wallet
- [ ] Create `insurance_subscriptions` record
- [ ] Monthly auto-debit (si auto_renew = true)

### I.5.3 – View subscriptions

- [ ] Endpoint GET `/api/insurance-subscriptions`
- [ ] Status, remaining coverage, next payment date

## I.6 Savings / Health savings account

### I.6.1 – Earmark savings

- [ ] Concept optionnel : parte de wallet destinée épargne santé
- [ ] Separate balance tracking
- [ ] Growth via interest (si applicable)

---

# PHASE J – Ordonnances & Dossier Médical

**Durée estimée** : 2–3 semaines
Pack pro santé : logiciel de gestion pour cliniques/pharmacies, intégration stock, facturation, et formation KYC; marketplace B2B pour hôpitaux.

## J.1 Prescription uploads

### J.1.1 – Upload ordonnance

- [ ] Endpoint POST `/api/orders/{id}/prescription`
- [ ] Image Upload (JPG, PNG, PDF)
- [ ] OCR processing (optionnel) pour auto-extract infos
- [ ] Store in S3
- [ ] Create `prescriptions` record (status = pending)

### J.1.2 – Prescription verification (Admin/Moderator)

- [ ] Endpoint GET `/api/admin/prescriptions/pending`
- [ ] Review image
- [ ] Endpoint PUT `/api/admin/prescriptions/{id}/verify`
  - Check validity (date, medical format)
  - Validate items dans ordonnance match order items
  - Approve ou reject
- [ ] Endpoint PUT `/api/admin/prescriptions/{id}/reject` + reason
- [ ] Notification client

### J.1.3 – Order flow avec prescription

- [ ] Si prescription required + not verified → order cannot be delivered
- [ ] Si verified → order proceed normalement
- [ ] Si rejected → refund

## J.2 Digital Medical Records

### J.2.1 – Add medical record

- [ ] Endpoint POST `/api/medical-records`
- [ ] record_type (diagnosis, treatment, lab_result, vaccination, etc.)
- [ ] Title, description
- [ ] Optional file upload
- [ ] Created by user ou medical provider
- [ ] Date of record

### J.2.2 – View records

- [ ] Endpoint GET `/api/medical-records`
- [ ] Filtrer par type, date range
- [ ] Décryptage de données sensibles (avec access logs)

### J.2.3 – Share records

- [ ] Endpoint POST `/api/medical-records/{id}/share`
- [ ] Select provider (doctor, clinic)
- [ ] Expiry date optionnel
- [ ] Create `medical_record_access`
- [ ] Audit log d'accès

### J.2.4 – Appointment scheduling (optionnel)

- [ ] Endpoint POST `/api/consultations`
- [ ] Sélectionner doctor + date/time
- [ ] Store in `consultations` table

---

# PHASE K – Paiements & Mobile Money

**Durée estimée** : 2–3 semaines

## K.1 Payment gateway integration

### K.1.1 – Stripe / Card payments (si applicable)

- [ ] Intégration Stripe Elements
- [ ] Tokenization (pas de stockage numéro carte)
- [ ] Webhooks pour notifications

### K.1.2 – Mobile Money integration (RDC specific)

**Orange Money** (si API dispo)

- [ ] API endpoint
- [ ] USSD fallback
- [ ] Webhook confirmation

**MTN MoMo** (si API dispo)

- [ ] API endpoint
- [ ] Webhook confirmation

**Wave** (si opérationnel en RDC)

- [ ] API integration

**Flutterwave** (gateway tiers)

- [ ] Support multiples payment methods locaux

### K.1.3 – Generic Mobile Money handler

- [ ] Créer classe abstrait PaymentGateway
- [ ] Implémenter pour chaque gateway
- [ ] Fallback graceful

## K.2 Processus de paiement

### K.2.1 – Initiate payment

- [ ] Endpoint POST `/api/payments/initiate`
- [ ] Order ID, amount, payment method
- [ ] Créer record `payment_transactions` (status = initiated)
- [ ] Retourner payment URL ou instruction

### K.2.2 – Payment processing

- [ ] Redirect client vers gateway
- [ ] Gateway process paiement
- [ ] Webhook callback à `/api/payments/webhook/[gateway]`
- [ ] Vérifier signature webhook
- [ ] Update `payment_transactions`
- [ ] Update order payment_status

### K.2.3 – Payment status checks

- [ ] Endpoint GET `/api/payments/{id}/status`
- [ ] Poll gateway pour status final

## K.3 Reconciliation

### K.3.1 – Automated reconciliation job

- [ ] Daily batch job (file-based queue)
- [ ] Fetch toutes transactions depuis gateway API
- [ ] Compare avec local records
- [ ] Flag discrepancies
- [ ] Auto-settle si confirmed

### K.3.2 – Settlement to merchants

- [ ] Daily/weekly payout à merchants
- [ ] Deduct commission, fees
- [ ] Créer entry dans `wallet_transactions` pour merchant wallet
- [ ] Notification merchant

---

# PHASE L – Administration & Modération

**Durée estimée** : 2 semaines

## L.1 Admin Dashboard

### L.1.1 – KPIs

- [ ] Endpoint GET `/api/admin/dashboard`
- [ ] Total revenue, orders, users
- [ ] Top merchants, top products
- [ ] Payment status breakdown

### L.1.2 – User management

- [ ] Endpoint GET `/api/admin/users` (search, filter)
- [ ] Endpoint PUT `/api/admin/users/{id}` (ban, unban, verify)
- [ ] View user wallets, orders, KYC status

## L.2 Modération contenu

### L.2.1 – Products moderation

- [ ] Endpoint GET `/api/admin/content/products/pending`
- [ ] Review flagged products
- [ ] Approve, reject, request changes

### L.2.2 – Reviews moderation

- [ ] Endpoint GET `/api/admin/content/reviews`
- [ ] Flag spammy/fake reviews
- [ ] Remove inappropriate content

### L.2.3 – Flags & Reports

- [ ] Endpoint GET `/api/admin/reports`
- [ ] Triage par type (fraud, product_issue, prescription_concern)
- [ ] Assign to moderator
- [ ] Status workflow (pending, investigating, resolved)

## L.3 KYC Management

### L.3.1 – KYC queue

- [ ] Endpoint GET `/api/admin/kyc/queue`
- [ ] Approve, reject, request more docs

### L.3.2 – Tier upgrades

- [ ] Endpoint GET `/api/admin/merchants/upgrade-requests`
- [ ] Review metrics (sales, rating, reviews)
- [ ] Approve tier upgrade

---

# PHASE M – Statistiques & Analytics

**Durée estimée** : 1–2 semaines

## M.1 Event tracking

### M.1.1 – Event logging

- [ ] Log page views, product views, searches
- [ ] Log cart/checkout events
- [ ] Store in `analytics_events` table
- [ ] Batch insert pour performance

### M.1.2 – User behavior analytics

- [ ] Funnels : view → cart → checkout → payment
- [ ] Funnel drop-off analysis
- [ ] Session duration, bounce rate
- [ ] Analytics santé anonymisées vendables aux institutions; dashboards pour pharmacies/cliniques; alertes épidémiologiques locales.

## M.2 Business intelligence

### M.2.1 – Sales analytics

- [ ] Endpoint GET `/api/analytics/sales` (daily, weekly, monthly)
- [ ] By category, merchant, product
- [ ] Revenue breakdown (commission, fees, net)

### M.2.2 – Medical data anonymization

- [ ] ETL : extract prescription data, anonymize
- [ ] Aggregate stats : prescriptions par médicament, top conditions
- [ ] Export CSV pour partenaires (pharmacies, cliniques)

### M.2.3 – Cohort analysis (optionnel)

- [ ] Endpoint GET `/api/analytics/cohorts`
- [ ] Retention, lifetime value

## M.3 Reporting

### M.3.1 – Report generation

- [ ] Scheduled reports (daily/weekly/monthly)
- [ ] Export PDF/Excel
- [ ] Email delivery

---

# PHASE N – Notifications & Emails

**Durée estimée** : 1 semaine

## N.1 Email notifications

### N.1.1 – Transactional emails

- [ ] Confirmation registration
- [ ] Email verification
- [ ] Password reset
- [ ] Order confirmation
- [ ] Payment confirmation
- [ ] Order status updates (confirmed, shipped, delivered)
- [ ] Support ticket updates

### N.1.2 – Promotional emails

- [ ] Special offers
- [ ] Personalized recommendations
- [ ] Weekly digest

### N.1.3 – Email template system

- [ ] ✅ API REST JSON (pas de template engine - API pure)
- [ ] Store templates en DB
- [ ] Variable substitution ({{name}}, {{order_id}})
- [ ] A/B testing support (optionnel)

## N.2 SMS notifications

### N.2.1 – SMS triggers

- [ ] OTP lors login avec 2FA
- [ ] Order status updates (optionnel)
- [ ] Delivery updates

### N.2.2 – SMS gateway

- [ ] Intégrer avec local SMS provider
- [ ] Fallback dial USSD (optionnel)

## N.3 In-app notifications

### N.3.1 – Notification center

- [ ] Endpoint GET `/api/notifications`
- [ ] List + mark as read
- [ ] Endpoint PUT `/api/notifications/{id}/read`

### N.3.2 – Push notifications (optionnel)

- [ ] Mobile app integration (Firebase Cloud Messaging)
- [ ] Store tokens, track delivery

---

# PHASE O – Sécurité & Conformité

**Durée estimée** : 2 semaines

## O.1 Sécurité informatique

### O.1.1 – HTTPS/TLS

- [ ] Certificate SSL (Let's Encrypt)
- [ ] TLS 1.2+ obligatoire
- [ ] HSTS headers

### O.1.2 – Authentication sécurité

- [ ] Password hashing bcrypt (cost=12)
- [ ] CSRF protection (token dans forms)
- [ ] CORS configuration (whitelist origins)
- [ ] Rate limiting (brute-force protection)

### O.1.3 – Injection prevention

- [ ] Prepared statements all DB queries
- [ ] Input validation (server-side)
- [ ] Output encoding HTML

### O.1.4 – Sensitive data

- [ ] Chiffrer médical data at rest (AES-256)
- [ ] Chiffrer API keys, payment tokens en DB
- [ ] Minimal logging de passwords, cards
- [ ] PCI-DSS compliance si card storage

### O.1.5 – API security

- [ ] API key validation
- [ ] JWT signature verification
- [ ] Rate limiting par API key/IP
- [ ] Request signing pour webhooks

## O.2 Conformité & Régulation

### O.2.1 – Législation RDC

- [ ] Vérifier liste médicaments autorisés (NEML)
- [ ] Respect réglementation pharmaceutique
- [ ] Document compliance matrix

### O.2.2 – Data protection

- [ ] GDPR-like data privacy policy
- [ ] User consent pour marketing emails
- [ ] Droit à l'oubli (data deletion pipeline)
- [ ] Right to export user data

### O.2.3 – Audit & Logging

- [ ] Audit trail KYC decisions
- [ ] Audit trail prescription verifications
- [ ] Medical record access logging
- [ ] Retain logs min. 1 year

### O.2.4 – Security testing

- [ ] Penetration testing (optionnel pré-launch)
- [ ] Code scanning (SAST)
- [ ] Dependency scanning (OWASP)

---

# PHASE P – Tests & QA

**Durée estimée** : 2 semaines

## P.1 Unit Tests

### P.1.1 – Models & Services

- [ ] Test User model (registration, login, password)
- [ ] Test Product model (search, filtering)
- [ ] Test Order service (creation, status updates)
- [ ] Test Wallet service (transactions, reserves)
- [ ] Test KYC submission logic
- [ ] Test Payment gateway integrations

### P.1.2 – Test coverage

- [ ] Target 70%+ code coverage
- [ ] Use PHPUnit
- [ ] Mock external services

## P.2 Integration Tests

### P.2.1 – Critical workflows

- [ ] User registration → email verification → login
- [ ] Merchant registration → KYC → product upload
- [ ] Customer : browse → add to cart → checkout → payment
- [ ] Prescription validation workflow
- [ ] Order delivery + livreur update
- [ ] Wallet top-up + payment via wallet

### P.2.2 – Payment tests

- [ ] Full payment flow (wallet, mobile money, card)
- [ ] Webhook handling
- [ ] Reconciliation

## P.3 End-to-end Tests

### P.3.1 – User journeys

- [ ] Use Cypress/Playwright
- [ ] Customer purchase journey
- [ ] Merchant product management
- [ ] Admin moderation workflows
- [ ] Livreur delivery process

## P.4 Performance Tests

### P.4.1 – Load testing

- [ ] k6 : simulate 100+ concurrent users
- [ ] Test API endpoints critical
- [ ] Search, checkout, payment processing
- [ ] Target < 500ms response time

## P.5 Security Testing

### P.5.1 – Manual security testing

- [ ] SAST scanning (SonarQube, Psalm)
- [ ] SQL injection tests
- [ ] XSS prevention verification
- [ ] CSRF token validation
- [ ] CORS config testing

---

# PHASE Q – Déploiement & Monitoring

**Durée estimée** : 1 semaine

## Q.1 Environnement production

### Q.1.1 – Préparation serveur

- [ ] VPS (DigitalOcean, Scaleway, IONOS, etc.) ou Shared Hosting PHP 8.1+
- [ ] OS : Ubuntu 20.04 LTS ou plus récent
- [ ] Web server : Nginx avec PHP-FPM
- [ ] SSL certificate (Let's Encrypt avec certbot)
- [ ] Fail2ban pour sécurité (DDoS, brute force)

### Q.1.2 – Database setup

- [ ] MySQL managed database (VPS MySQL ou managed service)
- [ ] Daily automated backups (mysqldump + scripts)
- [ ] Configuration replication (master-slave optionnel pour HA)
- [ ] Initial schema + seed data imported

### Q.1.3 – Storage

- [ ] S3 bucket (AWS S3 ou Minio local)
- [ ] CDN (CloudFront, Cloudflare)
- [ ] CORS configuration et vie des token d'accès

### Q.1.4 – Monitoring setup

- [ ] Logs fichier : `/var/log/afiazone/` avec rotation
- [ ] Sentry for error tracking (optionnel)
- [ ] Uptime monitoring (UptimeRobot, etc.)
- [ ] Alerts email sur erreurs critiques

## Q.2 CI/CD Pipeline

### Q.2.1 – GitHub Actions / GitLab CI

- [ ] On push to main :
  - Run tests (PHPUnit, integration)
  - Linting PHP (PHP CS Fixer, Psalm)
  - Code quality scan (optionnel)
  - Deploy artifact (git clone ou artifact)

### Q.2.2 – Staging deployment

- [ ] Auto-deploy to staging on PR
- [ ] Run smoke tests
- [ ] Manual approval before prod

### Q.2.3 – Production deployment

- [ ] Manual trigger
- [ ] Blue-green deployment (optionnel)
- [ ] Database migration management
- [ ] Rollback strategy

## Q.3 Operational procedures

### Q.3.1 – Database migrations

- [ ] Schema version management
- [ ] Zero-downtime migrations (optionnel, complex)
- [ ] Rollback scripts

### Q.3.2 – Deployment checklist

- [ ] Pre-deployment : backup DB, test recovery
- [ ] Deploy : new code version
- [ ] Post-deployment : smoke tests, monitoring
- [ ] Rollback criteria

### Q.3.3 – Monitoring dashboards

- [ ] Real-time API response times
- [ ] Error rates
- [ ] Database performance
- [ ] Payment processing status
- [ ] Queue job status

### Q.3.4 – Alerting

- [ ] Slack alerts for errors
- [ ] Email for critical failures
- [ ] On-call rotation setup

---

# PHASE R – Post-lancement & Optimisation

**Durée estimée** : Continu après lancement

## R.1 Stabilisation initiale

### R.1.1 – Monitoring actif

- [ ] 24/7 monitoring semaine 1
- [ ] Quick fixes pour bugs urgents
- [ ] Performance tuning basé métriques

### R.1.2 – User feedback

- [ ] Collect bug reports
- [ ] Support ticket triage
- [ ] Hot-fix deployment cycle rapide

## R.2 Optimisations

### R.2.1 – Performance tuning

- [ ] Database query optimization (slow query logs)
- [ ] Caching layer (file-based and memcached for sessions, query results)
- [ ] Image optimization + thumbnails
- [ ] API response time targets

### R.2.2 – Feature enhancements

- [ ] Based on user feedback
- [ ] Phase 2 features (wallet advanced features)
- [ ] UX improvements

## R.3 Scaling

### R.3.1 – Horizontal scaling

- [ ] Multiple app servers (load balancer)
- [ ] Database replication (read replicas)
- [ ] Cache cluster
- [ ] Queue workers scaling

### R.3.2 – Cost optimization

- [ ] Reserved instances (si cloud)
- [ ] Storage optimization
- [ ] CDN bandwidth optimization

---

# Dépendances & Intégrations Externes

## Paiements & Mobile Money

- **Stripe** (cartes, optionnel)
- **Orange Money API** (RDC)
- **MTN MoMo API** (Afrique)
- **Wave** (si dispo RDC)
- **Flutterwave** (gateway tiers)

## Email & SMS

- **SendGrid / Mailgun** (transactional emails)
- **Twilio** (SMS)
- **Mailhog** (local testing)

## Stockage fichiers

- **AWS S3** ou minio (local)
- **Cloudflare** (CDN)

## Analytics & Monitoring

- **Sentry** (error tracking)
- **Google Analytics** (user analytics)
- **Prometheus + Grafana** (infra metrics)

## Maps & Localisation

- **Google Maps API** (distance calculation)
- **Leaflet** (open source maps, optionnel)

---

# Critères de Succès par Phase

| Phase | KPI / Critère de succès                                    |
| ----- | ---------------------------------------------------------- |
| A     | Documenter complet, stakeholders alignment                 |
| B     | Schema DB validé, no circular relations                    |
| C     | Local dev setup done (Laragon), MySQL ready, bootstrap OK  |
| D     | JWT/Session auth working, RBAC enforced                    |
| E     | KYC workflow end-to-end (submit, review, approve)          |
| F     | Catalog accessible, search working, 1000+ products indexed |
| G     | Full order workflow, from cart to delivery                 |
| H     | Livreurs can track deliveries, clients receive updates     |
| I     | Wallet funding, transactions logged, balance correct       |
| J     | Prescriptions upload, verification workflow automated      |
| K     | Payments successful, reconciliation accurate               |
| L     | Admin can moderate content, view analytics                 |
| M     | Analytics events tracked, reports generated                |
| N     | Emails sent reliably, no spam                              |
| O     | Security audit passed, PCI-DSS ready                       |
| P     | 70% code coverage, e2e tests green                         |
| Q     | Production environment stable, CI/CD automated             |
| R     | <2% error rate, uptime >99.5%, user retention >60%         |
| S     | Blog opérationnel, articles publiés, SEO actif              |
| T     | Publicités diffusées, impressions/clics trackés, ROI mesuré  |
| U     | API tierces documentée, clés API distribuées, webhooks actifs |
| V     | 3 langues actives (FR, EN, SW), traductions complètes >95%   |

---

# Risques & Mitigation

| Risque                               | Probabilité | Impact   | Mitigation                                       |
| ------------------------------------ | ----------- | -------- | ------------------------------------------------ |
| Délais MB retard adoption fintech    | Moyen       | Haut     | MVP lean, partnership avec operateurs            |
| Intégration Mobile Money complexe    | Moyen       | Haut     | Commencer API, prévoir USSD fallback             |
| Frais paiement trop élevés           | Moyen       | Moyen    | Négocier rates, educate users                    |
| Prescription vérification bottleneck | Moyen       | Moyen    | Process automation (OCR), modérateurs parallèles |
| Performance sous charge              | Bas         | Haut     | Load testing, caching strategy, scaling plan     |
| Perte données utilisateur            | Très bas    | Critique | Backups automatiquement, disaster recovery test  |
| Compliance issues post-launch        | Bas         | Haut     | Legal review early, regulatory expert            |

---

# Roadmap post-MVP

1. **Semaine 1–4** : MVP merchant-facing + catalog
2. **Semaine 5–8** : Customer checkout + basic payment
3. **Semaine 9–12** : Wallet, prescriptions, delivery
4. **Semaine 13–16** : Payment integration, admin, tests
5. **Semaine 17–20** : Security hardening, optimizations, launch
6. **Semaine 21–24** : Blog, publicité in-app, API tierces parties
7. **Semaine 25–28** : Internationalisation (i18n), polish final, lancement complet

---

---

# PHASE S – Blog & Gestion de Contenu

**Durée estimée** : 1–2 semaines

## S.1 Système de Blog

### S.1.1 – Structure du blog

- [ ] Créer tables `blog_categories`, `blog_posts`, `blog_tags`, `blog_post_tags`, `blog_comments`
- [ ] Support des catégories hiérarchiques (parent_id)
- [ ] Tags multiples par article (many-to-many)
- [ ] Statut de publication : draft → pending_review → published → archived

### S.1.2 – CRUD Articles (Admin/Modérateur)

- [ ] Endpoint POST `/api/blog/posts` (créer article)
- [ ] Endpoint PUT `/api/blog/posts/{slug}` (modifier)
- [ ] Endpoint DELETE `/api/blog/posts/{id}` (supprimer / archiver)
- [ ] Endpoint GET `/api/admin/blog/posts` (liste admin avec filtres status)
- [ ] Support contenu riche (HTML sanitisé) avec images intégrées
- [ ] Upload images d'article (cover image + images dans le contenu)
- [ ] SEO : meta_title, meta_description, slug auto-généré
- [ ] Planification de publication (scheduled_at)

### S.1.3 – Affichage public

- [ ] Endpoint GET `/api/blog/posts` (liste publique, paginée)
- [ ] Endpoint GET `/api/blog/posts/{slug}` (détail article)
- [ ] Endpoint GET `/api/blog/categories` (liste catégories)
- [ ] Endpoint GET `/api/blog/categories/{slug}/posts` (articles par catégorie)
- [ ] Endpoint GET `/api/blog/tags/{slug}/posts` (articles par tag)
- [ ] Filtres : catégorie, tag, date, popularité
- [ ] Tri : newest, most_viewed, most_commented
- [ ] Recherche fulltext dans titre + contenu

### S.1.4 – Commentaires

- [ ] Endpoint POST `/api/blog/posts/{id}/comments` (ajouter commentaire)
- [ ] Endpoint GET `/api/blog/posts/{id}/comments` (liste commentaires)
- [ ] Endpoint DELETE `/api/blog/comments/{id}` (supprimer)
- [ ] Modération des commentaires (status : pending → approved / rejected)
- [ ] Support commentaires imbriqués (parent_id)
- [ ] Rate limiting : max 5 commentaires/minute par utilisateur
- [ ] Notification auteur quand nouveau commentaire

### S.1.5 – Statistiques du blog

- [ ] Compteur de vues par article (view_count)
- [ ] Articles les plus populaires
- [ ] Intégration avec module Analytics (analytics_events)

---

# PHASE T – Publicité In-App

**Durée estimée** : 1–2 semaines

## T.1 Gestion des campagnes publicitaires

### T.1.1 – Création de campagnes

- [ ] Créer tables `ad_campaigns`, `ad_placements`, `ad_impressions`, `ad_clicks`
- [ ] Endpoint POST `/api/ads/campaigns` (créer campagne)
- [ ] Endpoint PUT `/api/ads/campaigns/{id}` (modifier)
- [ ] Endpoint GET `/api/admin/ads/campaigns` (liste admin)
- [ ] Types de campagne : banner, sidebar, featured_product, popup, interstitial
- [ ] Ciblage : par catégorie de produit, localisation, type d'utilisateur
- [ ] Budget : daily_budget, total_budget
- [ ] Planification : start_date → end_date
- [ ] Statut : draft → active → paused → completed → cancelled

### T.1.2 – Emplacements publicitaires

- [ ] Endpoint GET `/api/ads/placements` (emplacements disponibles)
- [ ] Emplacements prédéfinis :
  - `homepage_banner` — Bannière page d'accueil
  - `category_sidebar` — Sidebar catégorie
  - `product_detail_related` — Suggestion produit sponsorisé
  - `search_results_top` — Haut des résultats de recherche
  - `checkout_suggestion` — Suggestion au checkout
  - `blog_inline` — Dans les articles de blog
- [ ] Pricing par emplacement (CPM, CPC, forfait)

### T.1.3 – Diffusion des publicités

- [ ] Endpoint GET `/api/ads/serve?placement=xxx` (servir une publicité)
- [ ] Sélection basée sur : budget restant, ciblage, priorité, randomisation
- [ ] Rotation des publicités (éviter fatigue publicitaire)
- [ ] Fréquence max par utilisateur (frequency_cap)
- [ ] Vérification budget avant affichage

### T.1.4 – Tracking impressions & clics

- [ ] Endpoint POST `/api/ads/impressions` (enregistrer impression)
- [ ] Endpoint POST `/api/ads/clicks/{id}` (enregistrer clic + redirect)
- [ ] Tracking côté serveur (éviter ad-blockers)
- [ ] Compteur en temps réel (daily_impressions, daily_clicks)
- [ ] Protection anti-fraude : déduplification par session, rate limiting

### T.1.5 – Reporting & facturation

- [ ] Endpoint GET `/api/ads/campaigns/{id}/stats` (statistiques campagne)
- [ ] Métriques : impressions, clics, CTR, coût total, conversions
- [ ] Facturation automatique marchands (débit wallet ou facturation)
- [ ] Rapport mensuel pour annonceurs
- [ ] Dashboard admin : revenus publicitaires, top campagnes

---

# PHASE U – API Tierces Parties

**Durée estimée** : 1–2 semaines

## U.1 Système de clés API

### U.1.1 – Gestion des clients API

- [ ] Créer tables `api_clients`, `api_client_permissions`, `api_webhooks`, `api_webhook_logs`
- [ ] Endpoint POST `/api/admin/api-clients` (créer client API)
- [ ] Endpoint GET `/api/admin/api-clients` (lister clients)
- [ ] Endpoint PUT `/api/admin/api-clients/{id}` (modifier)
- [ ] Endpoint DELETE `/api/admin/api-clients/{id}` (révoquer)
- [ ] Génération de paire api_key + api_secret (hash sécurisé)
- [ ] Support environnements : sandbox (test) et production
- [ ] Rate limiting par client (requests_per_minute, requests_per_day)

### U.1.2 – Permissions API

- [ ] Permissions granulaires par client :
  - `products.read` — Lecture catalogue
  - `products.write` — Gestion catalogue
  - `orders.read` — Consultation commandes
  - `orders.write` — Création/modification commandes
  - `inventory.read` — Consultation stock
  - `inventory.write` — Mise à jour stock
  - `users.read` — Consultation profils (limité)
  - `analytics.read` — Accès données analytics
- [ ] Scoping par merchant_id (un client API peut être limité à un marchand)

### U.1.3 – Authentification API

- [ ] Authentication via header `X-API-Key` + `X-API-Secret`
- [ ] Ou via Bearer token (JWT signé avec api_secret)
- [ ] Middleware dédié `ApiKeyMiddleware`
- [ ] Logging de toutes les requêtes API dans `api_logs`
- [ ] Réponse 401 si clé invalide, 429 si rate limit atteint

## U.2 Webhooks sortants

### U.2.1 – Configuration webhooks

- [ ] Endpoint POST `/api/api-clients/{id}/webhooks` (enregistrer webhook)
- [ ] Événements supportés :
  - `order.created`, `order.updated`, `order.cancelled`, `order.delivered`
  - `payment.completed`, `payment.failed`, `payment.refunded`
  - `product.created`, `product.updated`, `product.out_of_stock`
  - `inventory.low_stock`, `inventory.updated`
  - `kyc.approved`, `kyc.rejected`
- [ ] URL de callback + secret partagé pour signature
- [ ] Retry policy : 3 tentatives avec backoff exponentiel (1min, 5min, 30min)

### U.2.2 – Envoi & logging webhooks

- [ ] Signature HMAC-SHA256 du payload avec le secret partagé
- [ ] Header `X-Webhook-Signature` pour vérification côté client
- [ ] Logging dans `api_webhook_logs` (payload, response, status_code, latency)
- [ ] Dashboard admin : taux de succès webhooks, erreurs fréquentes
- [ ] Endpoint GET `/api/api-clients/{id}/webhooks/logs` (historique livraisons)

## U.3 Documentation API

### U.3.1 – Documentation OpenAPI

- [ ] Générer spec OpenAPI 3.0 (Swagger)
- [ ] Portail développeur public avec documentation interactive
- [ ] Exemples de requêtes/réponses pour chaque endpoint
- [ ] Guide d'intégration (getting started)
- [ ] Sandbox pour tests (environnement isolé)

---

# PHASE V – Internationalisation (i18n)

**Durée estimée** : 1–2 semaines

## V.1 Infrastructure multilingue

### V.1.1 – Structure de base

- [ ] Créer tables `languages`, `translations`
- [ ] Langues initiales :
  - **Français** (fr) — Langue par défaut
  - **Anglais** (en)
  - **Swahili** (sw)
- [ ] Architecture extensible pour ajouter facilement de nouvelles langues
- [ ] Ajouter colonne `preferred_locale` à `user_profiles`
- [ ] Détection automatique de la langue :
  1. Paramètre URL (`?lang=en`)
  2. Header HTTP `Accept-Language`
  3. Préférence utilisateur (si connecté)
  4. Langue par défaut (fr)

### V.1.2 – Système de traduction

- [ ] Clés de traduction hiérarchiques (namespace.group.key)
  - Exemple : `auth.login.title`, `product.detail.add_to_cart`
- [ ] Endpoint GET `/api/translations/{locale}` (récupérer traductions)
- [ ] Endpoint GET `/api/translations/{locale}/{namespace}` (par namespace)
- [ ] Support des placeholders : `Bonjour {name}` → `Hello {name}`
- [ ] Support des pluriels : `{count} produit|{count} produits`
- [ ] Fallback : si traduction absente → langue par défaut (fr)
- [ ] Cache des traductions (file-based) avec invalidation

### V.1.3 – Helper i18n côté serveur

- [ ] Fonction helper `__('auth.login.title')` pour traduction
- [ ] Fonction helper `__n('product.count', $count)` pour pluriels
- [ ] Middleware `LocaleMiddleware` pour détecter et appliquer la langue
- [ ] Header de réponse `Content-Language` automatique
- [ ] Locale dans les réponses JSON API

## V.2 Traduction du contenu

### V.2.1 – Contenu statique (UI)

- [ ] Traduction de tous les labels, messages, boutons de l'interface
- [ ] Messages d'erreur traduits (validation, auth, paiement)
- [ ] Emails transactionnels traduits (confirmation, reset password, etc.)
- [ ] Notifications traduites
- [ ] Pages légales (CGU, politique de confidentialité) en 3 langues

### V.2.2 – Contenu dynamique

- [ ] Traduction des catégories de produits
- [ ] Traduction des descriptions produits (optionnel, si marchand fournit)
- [ ] Traduction des articles de blog
- [ ] Support `translation_key` sur tables à contenu traduisible :
  - `product_categories.name` → clé dans `translations`
  - `blog_categories.name` → clé dans `translations`

### V.2.3 – Administration des traductions

- [ ] Endpoint POST `/api/admin/translations` (ajouter traduction)
- [ ] Endpoint PUT `/api/admin/translations/{id}` (modifier)
- [ ] Endpoint DELETE `/api/admin/translations/{id}` (supprimer)
- [ ] Endpoint GET `/api/admin/translations/missing/{locale}` (traductions manquantes)
- [ ] Import/export traductions en format JSON ou CSV
- [ ] Interface admin pour gestion des traductions (liste, recherche, édition inline)
- [ ] Progression de traduction par langue (% complété)

### V.2.4 – Gestion des langues

- [ ] Endpoint POST `/api/admin/languages` (ajouter une nouvelle langue)
- [ ] Endpoint PUT `/api/admin/languages/{code}` (activer/désactiver)
- [ ] Endpoint GET `/api/languages` (langues disponibles, public)
- [ ] Possibilité de désactiver temporairement une langue incomplète
- [ ] Associer un drapeau/icône à chaque langue

## V.3 Formatage localisé

### V.3.1 – Formats régionaux

- [ ] Formatage des prix selon la locale (devise, séparateurs)
  - FR : `1 234,56 $` | EN : `$1,234.56` | SW : `$1,234.56`
- [ ] Formatage des dates selon la locale
  - FR : `12 mars 2026` | EN : `March 12, 2026` | SW : `12 Machi 2026`
- [ ] Formatage des nombres
- [ ] Support RTL (right-to-left) préparé pour langues futures (arabe, etc.)

---

_FIN DU PLAN COMPLET_

Document généré pour le projet **AfiaZone** – Medical Marketplace avec E-Wallet Santé.
Pour questions ou clarifications, contacter l'équipe de produit.
