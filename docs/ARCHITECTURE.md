# 📐 Architecture — AfiaZone

**Medical Marketplace avec E-Wallet Santé**  
**Version** : 1.0  
**Date** : Mars 2026  
**Stack** : PHP 8.1+ (MVC Custom) | MySQL 8 | File-based Caching

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture applicative](#architecture-applicative)
3. [Architecture technique](#architecture-technique)
4. [Structure des répertoires](#structure-des-répertoires)
5. [Modules métier](#modules-métier)
6. [Flux de données](#flux-de-données)
7. [Sécurité & Authentification](#sécurité--authentification)
8. [Base de données](#base-de-données)
9. [Déploiement & Infrastructure](#déploiement--infrastructure)

---

## Vue d'ensemble

**AfiaZone** est une plateforme de marketplace médicale avec un système d'e-wallet intégré, conçue pour la région d'Afrique centrale (RDC).

### ⚠️ Important : Architecture sans framework

Ce projet est une **API REST pure en PHP pur** :
- ✅ **Pas de framework** (Laravel, Symfony, etc.)
- ✅ **Pas de template engine** (Blade, Twig, etc.)  
- ✅ **Pas de base de données ORM** (Eloquent, Doctrine, etc.)
- ✅ **Réponses JSON uniquement** - pas de rendu HTML
- ✅ **MVC personnalisé** léger et contrôlé
- ✅ **Router intégré** dans `/index.php`
- ✅ **Dépendances minimales** via Composer

Les frontend (web, mobile) consomment cette API en tant que clients HTTP.

### Elle permet :

- **Patients** : Acheter des médicaments, consultation médicale, gestion du dossier médical
- **Marchands** : Vendre des produits pharmaceutiques (grossistes, producteurs, détaillants)
- **Livreurs** : Assurer la livraison et le suivi des commandes
- **Administrateurs** : Modération, KYC, gestion des marchands, analytics

### Caractéristiques clés

✅ Marketplace multi-vendeur  
✅ E-wallet avec paiement mobile money  
✅ Gestion des prescriptions médicales  
✅ Dossier médical électronique (EMR)  
✅ Système de livraison avec suivi GPS  
✅ KYC (Know Your Customer) intégré  
✅ Système de tiers (niveaux marchands)  
✅ Double-entry bookkeeping pour wallet  

---

## Architecture applicative

### Pattern MVC Personnalisé

L'application suit un pattern **MVC modulaire sans framework** avec couches métier séparées :

```
/app
├── /Controllers          # Gestion des requêtes HTTP
│   ├── ProductController.php
│   ├── OrderController.php
│   ├── WalletController.php
│   ├── UserController.php
│   ├── AuthController.php
│   ├── AcademyController.php
│   ├── CalendarController.php
│   └── ...
├── /Models              # Entités métier & accès données
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   ├── Wallet.php
│   ├── Merchant.php
│   ├── Shipment.php
│   └── ...
├── /Services            # Logique métier isolée
│   ├── WalletService.php
│   ├── PaymentService.php
│   ├── KycService.php
│   ├── OrderService.php
│   ├── DeliveryService.php
│   ├── PrescriptionService.php
│   └── ...
├── /Repositories        # Accès données abstraits
│   ├── UserRepository.php
│   ├── ProductRepository.php
│   └── ...
├── /Middleware          # Pipeline de requête
│   ├── AuthMiddleware.php
│   ├── RbacMiddleware.php
│   ├── RateLimitMiddleware.php
│   ├── CorsMiddleware.php
│   └── LoggingMiddleware.php
├── /Validators          # Validation métier
│   ├── ProductValidator.php
│   ├── OrderValidator.php
│   └── ...
├── /Exceptions          # Exceptions custom
│   ├── ValidationException.php
│   ├── UnauthorizedException.php
│   ├── NotFoundException.php
│   └── ...
├── /Helpers             # Fonctions utilitaires
│   └── ...
└── /Console             # Commandes CLI
    ├── MigrateCommand.php
    ├── SeedCommand.php
    └── ...

/routes
├── api.php              # Routes API (v1, v2, ...)
└── web.php              # Routes web/frontend

/config
├── database.php         # Configuration BDD
├── app.php             # Paramètres globaux
├── services.php        # Configuration services externes
└── cache.php           # Configuration Cache/Session/Queue (file-based)

/index.php             # Point d'entrée (router API REST)

/html
├── /back               # Templates HTML optionnels (admin panel future)
├── /front              # Templates HTML optionnels (frontend future)
└── /...                # Pages HTML statiques (non utilisées par l'API)

/public                # Fichiers statiques (CSS, JS, images)
│
/assets
├── /css                # Feuilles de style
├── /js                 # Scripts JavaScript
└── /img                # Images (avatars, backgrounds, produits, etc.)

/database
├── schema.sql          # Structure BDD complète
└── /migrations         # Migrations versionnées

/tests
├── /Unit               # Tests unitaires
└── /Feature            # Tests d'intégration

/bin
├── setup.php           # Setup initial
└── setup-db.php        # Initialisation BDD
```

### Principes architecturaux

| Concept | Description |
|---------|-------------|
| **Séparation des responsabilités** | Controllers → Services → Repositories → Models |
| **Dependency Injection** | Injection de dépendances via constructeur |
| **Service Layer** | Toute logique métier centralisée dans Services |
| **Repository Pattern** | Accès données abstraits via Repositories |
| **DTOs** | Data Transfer Objects pour API responses |
| **Middleware Pipeline** | Gestion déclarative du flux requête |
| **Exception Handling** | Exceptions custom organisées par domaine |

---

## Architecture technique

### Stack technologique

```
┌─────────────────────────────────────────────┐
│           Client (Web Browser)               │
│  HTML5 + Bootstrap 5 + jQuery/Vanilla JS    │
└────────────────┬────────────────────────────┘
                 │ HTTP/HTTPS
┌────────────────▼──────────────────────────────┐
│         index.php (Router Principal)          │
│  Dispatcher requête vers Controllers          │
└────────────────┬───────────────────────────────┘
                 │
┌────────────────▼──────────────────────────────────────────┐
│              PHP Application Layer                         │
│  ┌───────────────────────────────────────────────────┐   │
│  │ Middleware (CORS, Auth, RBAC, Rate Limit)        │   │
│  └───────────────────────────────────────────────────┘   │
│  ┌───────────────────────────────────────────────────┐   │
│  │ Controllers (ProductController, OrderController) │   │
│  └───────────────────────────────────────────────────┘   │
│  ┌───────────────────────────────────────────────────┐   │
│  │ Services (WalletService, PaymentService, etc.)   │   │
│  └───────────────────────────────────────────────────┘   │
│  ┌───────────────────────────────────────────────────┐   │
│  │ Repositories (UserRepository, ProductRepository) │   │
│  └───────────────────────────────────────────────────┘   │
└────────────────┬─────────────────────────────────────────┘
                 │
       ┌─────────┴──────────────┐
       │                        │
   ┌───▼────┐          ┌───▼──────────┐
   │ MySQL  │          │  File Cache  │
   │  8.0+  │          │Sessions/     │
   │        │          │Queues        │
   └────────┘          └──────────────┘
```

### Composants clés

#### 1. **PHP Application (Core)**
- **Langage** : PHP 8.1+ (strict types)
- **Pattern** : MVC Personnalisé sans framework lourd
- **Router** : `index.php` à la racine (dispatcher)
- **Coding Standards** : PSR-12
- **Autoloading** : Composer PSR-4

#### 2. **Base de données**
- **SGBDR** : MySQL 8.0+
- **Encoding** : UTF-8 MB4
- **Engine** : InnoDB
- **Transactions** : Support ACID complet
- **Indexation** : Optimisée par requête fréquente

#### 3. **Cache & Queues**
- **Redis** : 
  - Caching (sessions, données métier)
  - Job queues (emails, traitements longs)
  - Real-time features (notifications)

#### 4. **Stockage fichiers**
- **S3 Compatible** (AWS S3, Minio)
- **Uploads** : Photos produits, documents KYC, prescriptions

#### 5. **Frontend**
- **Architecture** : HTML5 + CSS3 + Vanilla JavaScript / jQuery
- **UI Kit** : Bootstrap 5 + Tabler Icons + Tabler UI Components
- **Templates** : Serveurs depuis `/html/` (rendu backend PHP)
- **Assets** : CSS, JS et images dans `/assets/`

---

## Structure des répertoires

```
afiazone/
├── index.php           # POINT D'ENTRÉE PRINCIPAL (router)
├── app/
│   ├── helpers.php
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   ├── Middleware/
│   ├── Validators/
│   ├── Exceptions/
│   └── Console/
├── routes/
│   ├── api.php
│   └── web.php (optionnel)
├── config/
│   ├── app.php
│   ├── database.php
│   └── services.php
├── html/               # TEMPLATES & PAGES
│   ├── /back           # Admin dashboards, gestion
│   ├── /front          # Pages clients
│   └── ...pages HTML
├── assets/
│   ├── /css
│   ├── /js
│   ├── /img            # Images (avatars, backgrounds, produits)
│   ├── /json
│   ├── /audio
│   └── /svg
├── js/                 # Scripts globaux
│   ├── bootstrap.js
│   ├── menu.js
│   └── ...
├── fonts/              # Polices & icônes
├── libs/               # Dépendances externes
├── database/
│   └── schema.sql
├── bin/
│   ├── setup.php
│   └── setup-db.php
├── tests/
│   ├── bootstrap.php
│   └── Unit/
├── docs/
│   ├── ARCHITECTURE.md (ce fichier)
│   ├── PLAN-COMPLET.md (roadmap complet)
│   └── plan.md
├── tasks/              # Scripts build
├── composer.json
├── psalm.xml
└── README.md
```

---

## Modules métier

### 1. Module Authentification & Autorisation (Phase D)

**Responsabilités** :
- Inscription/Login via email ou phone
- Vérification email & OTP
- Reset de mot de passe
- Gestion des sessions JWT
- Role-Based Access Control (RBAC)
- Two-Factor Authentication

**Tables clés** :
- `users` — Compte utilisateur
- `roles` — Rôles (customer, merchant, driver, admin)
- `permissions` — Permissions granulaires
- `user_roles` — Associations utilisateur-rôles
- `tokens` — JWT, email verification, password reset

**Controllers/Services** :
- `AuthController` → `AuthService`
- `AuthMiddleware` — Validation JWT/Session
- `RbacMiddleware` — Vérification permissions

---

### 2. Module Utilisateurs & KYC (Phase E)

**Responsabilités** :
- Gestion profils utilisateurs
- KYC (Know Your Customer) multi-niveaux
- Vérification documents (ID, adresse, business)
- Tiers marchands (verified → premium → gold → diamond)
- Modération utilisateurs

**Tables clés** :
- `user_profiles` — Infos détaillées utilisateur
- `kyc_submissions` — Soumissions KYC
- `kyc_documents` — Documents KYC
- `merchant_tiers` — Niveaux marchands

**Services** :
- `KycService` — Workflow KYC
- `UserService` — Gestion profils

---

### 3. Module Catalogue Produits (Phase F)

**Responsabilités** :
- Gestion produits (création, édition, suppression)
- Catégorisation & moteur de recherche
- Variants produits (taille, dosage, etc.)
- Stock marchand
- Images produits
- Attributs produits

**Tables clés** :
- `products` — Catalogue produits
- `product_categories` — Hiérarchie catégories
- `product_images` — Images & média
- `product_variants` — Variants (S/M/L, dosages)
- `product_attributes` — Attributs additionnels
- `merchant_stocks` — Stock par marchand

**Services** :
- `ProductService` — Gestion produits
- `SearchService` — Recherche FULLTEXT
- `StockService` — Gestion stock

---

### 4. Module Panier & Commandes (Phase G)

**Responsabilités** :
- Gestion panier (add/remove/update)
- Création commandes
- Statuts commandes (pending → delivered)
- Gestion remises & coupons
- Suivi commandes client

**Tables clés** :
- `shopping_carts` — Paniers
- `shopping_cart_items` — Articles panier
- `orders` — Commandes
- `order_items` — Détail commande
- `order_status_logs` — Historique statuts
- `delivery_addresses` — Adresses livraison

**Services** :
- `CartService` — Gestion panier
- `OrderService` — Orchestre commande (cart → paiement → livraison)
- `DiscountService` — Calcul remises

---

### 5. Module Livraison (Phase H)

**Responsabilités** :
- Gestion partenaires logistiques
- Assignation livreurs
- Suivi en temps réel (GPS)
- Historique livraison
- Confirmation livraison (signature, photos)
- Gestion retours

**Tables clés** :
- `delivery_providers` — Partenaires logistiques
- `delivery_personnel` — Livreurs
- `shipments` — Livraisons
- `shipment_tracking_logs` — Historique GPS

**Services** :
- `DeliveryService` — Orchestration livraison
- `TrackingService` — Suivi en temps réel

---

### 6. Module E-Wallet Santé (Phase I)

**Responsabilités** :
- Création wallets utilisateurs
- Top-up via card/mobile money
- Paiements via wallet
- Réservations de fonds
- Historique transactions
- Solde disponible vs réservé

**Tables clés** :
- `wallets` — Porte-monnaie utilisateurs
- `wallet_transactions` — Transactions (double-entry)
- `wallet_topups` — Dépôts
- `wallet_reservations` — Réservations
- `wallet_balance_history` — Audit trail

**Services** :
- `WalletService` — Gestion wallet
- `TransactionService` — Transactions (atomique)

**Sécurité** :
- Double-entry bookkeeping
- Audit trail complet
- Validation montants
- Gestion décimales (DECIMAL(14,2))

---

### 7. Module Prescriptions & Dossier Médical (Phase J)

**Responsabilités** :
- Upload & vérification prescriptions
- Dossier médical électronique (EMR)
- Partage d'accès médical
- Consultation médicale
- Suivi prescriptions (valides/expirées)

**Tables clés** :
- `prescriptions` — Ordonnances médicales
- `medical_records` — Dossier médical
- `medical_record_access` — Partage d'accès
- `consultations` — Rendez-vous médicaux

**Services** :
- `PrescriptionService` — Vérification ordonnances
- `MedicalRecordService` — Gestion EMR

---

### 8. Module Paiements & Mobile Money (Phase K)

**Responsabilités** :
- Intégration passerelles paiement
- Paiement cash on delivery
- Paiement card (Stripe, etc.)
- Mobile money (Airtel, Vodacom, Orange)
- Refunds & disputes
- Réconciliation paiements

**Tables clés** :
- `user_payment_methods` — Méthodes de paiement
- `payment_transactions` — Transactions paiement
- `payment_disputes` — Litiges paiement

**Services** :
- `PaymentService` — Orchestration paiement
- `MobileMoneyService` — Intégration opérateurs
- `RefundService` — Gestion remboursements

---

### 9. Module Administration & Modération (Phase L)

**Responsabilités** :
- Dashboard admin
- Gestion marchands (suspension, ban)
- Modération contenu
- Gestion utilisateurs
- Analytics plateforme
- Paramètres système

**Permissions** :
- Admin.Dashboard
- Admin.Users.Manage
- Admin.Merchants.Manage
- Admin.Moderation
- Admin.Settings

---

### 10. Module Statistiques & Analytics (Phase M)

**Responsabilités** :
- Dashboards (sales, users, orders)
- Reports générés
- Métriques clés (KPIs)
- Données temps réel vs historiques

**Services** :
- `AnalyticsService` — Calcul KPIs
- `ReportService` — Génération rapports

---

### 11. Module Notifications & Emails (Phase N)

**Responsabilités** :
- Notifications en temps réel (WebSocket/Server-Sent Events)
- Emails transactionnels
- SMS (optionnel)
- Système de queue asynchrone

**Infrastructure** :
- Redis Pub/Sub — Broadcasting notifications
- Queue job — Traitement emails asynchrone
- Mailhog/SendGrid — Service email

---

### 12. Sécurité & Conformité (Phase O)

**Responsabilités** :
- HTTPS/TLS obligatoire
- CSRF tokens
- Input validation & sanitization
- SQL injection prevention (prepared statements)
- XSS prevention (output encoding)
- Rate limiting
- CORS policy
- Data encryption (sensibles)
- RGPD / Conformité locale RDC
- Audit logs

**Middleware & Services** :
- `CsrfMiddleware` — CSRF protection
- `RateLimitMiddleware` — Rate limiting
- `EncryptionService` — Chiffrement données

---

### 13. Module Blog & Gestion de Contenu (Phase S)

**Responsabilités** :
- Publication d'articles (santé, bien-être, actualités médicales)
- Catégorisation & tagging d'articles
- Commentaires imbriqués avec modération
- SEO (meta_title, meta_description, slug)
- Planification de publications
- Statistiques (vues, articles populaires)

**Tables clés** :
- `blog_categories` — Catégories de blog (hiérarchiques)
- `blog_posts` — Articles de blog
- `blog_tags` — Tags
- `blog_post_tags` — Pivot article↔tag
- `blog_comments` — Commentaires (imbriqués)

**Controllers/Services** :
- `BlogController` → `BlogService`
- `CommentService` — Gestion commentaires

---

### 14. Module Publicité In-App (Phase T)

**Responsabilités** :
- Gestion des campagnes publicitaires (marchands)
- Emplacements prédéfinis (banner, sidebar, featured_product, etc.)
- Diffusion basée sur ciblage & budget
- Tracking impressions & clics
- Facturation & reporting
- Protection anti-fraude (déduplification, rate limiting)

**Tables clés** :
- `ad_campaigns` — Campagnes publicitaires
- `ad_placements` — Emplacements (homepage_banner, sidebar, etc.)
- `ad_campaign_placements` — Pivot campagne↔emplacement
- `ad_impressions` — Impressions trackées
- `ad_clicks` — Clics trackés

**Controllers/Services** :
- `AdController` → `AdService`
- `AdTrackingService` — Impressions, clics, stats

---

### 15. Module API Tierces Parties (Phase U)

**Responsabilités** :
- Gestion des clients API (clés, secrets, permissions)
- Authentification via API key / Bearer token
- Rate limiting par client
- Webhooks sortants (order.created, payment.completed, etc.)
- Signature HMAC-SHA256 des webhooks
- Documentation OpenAPI / Swagger
- Support environnements sandbox & production

**Tables clés** :
- `api_clients` — Clients API tiers
- `api_client_permissions` — Permissions granulaires
- `api_webhooks` — Webhooks enregistrés
- `api_webhook_logs` — Historique livraisons webhook

**Middleware/Services** :
- `ApiKeyMiddleware` — Validation clé API
- `WebhookService` — Envoi & retry webhooks
- `ApiClientService` — Gestion clients

---

### 16. Module Internationalisation / i18n (Phase V)

**Responsabilités** :
- Support multilingue : Français (défaut), Anglais, Swahili
- Architecture extensible (ajouter des langues facilement)
- Clés de traduction hiérarchiques (namespace.group.key)
- Détection langue (URL param → Accept-Language → préférence user → défaut)
- Fallback vers langue par défaut si traduction absente
- Formatage localisé (dates, prix, nombres)
- Traduction contenu dynamique (catégories, articles blog)
- Cache des traductions (file-based)

**Tables clés** :
- `languages` — Langues supportées (fr, en, sw...)
- `translations` — Clés de traduction
- `user_profiles.preferred_locale` — Préférence utilisateur

**Middleware/Services** :
- `LocaleMiddleware` — Détection & application de la langue
- `TranslationService` — Résolution des traductions
- Helper `__('key')` — Fonction de traduction
- Helper `__n('key', $count)` — Pluralisation

---

## Flux de données

### Flux 1 : Authentification

```
User Login Request
       ↓
AuthController::login()
       ↓
AuthService::authenticate()
    - Validation email/password
    - Hash verification (password_verify)
    ↓
JWT Token Generation
       ↓
Response + Token Cookie
```

### Flux 2 : Création Commande

```
Add to Cart
       ↓
CartService::addItem()
    - Cache session
       ↓
Checkout
       ↓
OrderController::createOrder()
    - Validation items
    - Vérification prescriptions
       ↓
OrderService::create()
    - Réservation fonds wallet
    - Création order_items
    - Initialiser livraison
       ↓
PaymentService::process()
    - Débit wallet/mobile money
    - Mettre à jour order.payment_status
       ↓
DeliveryService::assignShipment()
    - Créer shipment
    - Assigner livreur
       ↓
NotificationService::orderCreated()
    - Email client
    - Email marchand
    - Push notification
       ↓
Response avec Order ID
```

### Flux 3 : Paiement Wallet

```
User initiate payment
       ↓
WalletService::reserve()
    - Check available_balance
    - Create reservation record
    - Update wallet (reserved_balance)
       ↓
PaymentService::charge()
    - Debit transaction
    - Update wallets (seller + platform fees)
    ↓
WalletService::release() [if refund]
    - Release reservation
    - Refund transaction
       ↓
AuditLog entry
```

### Flux 4 : Livraison avec suivi GPS

```
Order created & assigned
       ↓
Livreur accepte course
       ↓
Shipment status = "picked_up"
       ↓
GPS updates (real-time via Redis)
    - Poll device GPS
    - TrackingService::updateLocation()
    - Broadcast via WebSocket
       ↓
Delivered & customer confirmation
    - Signature capture
    - Photo proof
       ↓
Order status = "delivered"
    - Settlement marchand
    - Deblocking wallet
```

---

## Sécurité & Authentification

### Authentification

| Méthode | Usage |
|---------|-------|
| **JWT** | API stateless, mobile apps |
| **Session cookie** | Web browser sessions |
| **API keys** | Services externes intégrations |
| **OAuth 2.0** | Connexion tierce (futur) |
| **2FA** | Admin & merchants (optionnel) |

### Authorization (RBAC)

```
User
├── Roles (many-to-many)
│   ├── customer
│   │   └── Permissions
│   │       ├── orders.create
│   │       ├── wallet.view
│   │       └── ...
│   ├── merchant
│   │   └── Permissions
│   │       ├── products.manage
│   │       ├── orders.view
│   │       └── ...
│   ├── driver
│   └── admin
```

### Middleware de sécurité

```php
// app/Middleware
├── AuthMiddleware.php          // JWT/Session validation
├── RbacMiddleware.php          // Permission check
├── CsrfMiddleware.php          // CSRF tokens
├── RateLimitMiddleware.php     // Rate limiting (Redis)
├── CorsMiddleware.php          // Cross-Origin
└── LoggingMiddleware.php       // Audit logging
```

### Bonnes pratiques

✅ Passwords hashed avec `password_hash()` (Argon2)  
✅ Prepared statements (PHP PDO)  
✅ Input validation + sanitization  
✅ Output encoding (JSON, HTML)  
✅ HTTPS/TLS obligatoire  
✅ CORS policy restrictive  
✅ Session timeout  
✅ Encryption données sensibles (wallets, prescriptions)  
✅ Audit logs complets  

---

## Base de données

### Schéma général (55+ tables)

#### Bloc 1 : Authentification & Utilisateurs
- `users` — Comptes utilisateurs
- `roles` — Rôles
- `permissions` — Permissions
- `user_roles`, `role_permissions` — Associations
- `user_profiles` — Profils détaillés
- `tokens` — JWT, verification tokens

#### Bloc 2 : KYC & Modération
- `kyc_submissions` — Soumissions KYC
- `kyc_documents` — Documents KYC
- `merchant_tiers` — Niveaux marchands

#### Bloc 3 : Marchands
- `merchants` — Profils marchands
- `merchant_shipping_info` — Infos livraison
- `merchant_fees` — Configuration frais

#### Bloc 4 : Produits & Catalogue
- `product_categories` — Catégories
- `products` — Produits
- `product_images` — Images
- `product_attributes` — Attributs
- `product_variants` — Variants (S/M/L)
- `merchant_stocks` — Stock par marchand

#### Bloc 5 : Commandes & Panier
- `shopping_carts` — Paniers
- `shopping_cart_items` — Articles panier
- `orders` — Commandes
- `order_items` — Détail commandes
- `order_status_logs` — Historique statuts
- `delivery_addresses` — Adresses livraison

#### Bloc 6 : Livraison
- `delivery_providers` — Partenaires logistiques
- `delivery_personnel` — Livreurs
- `shipments` — Livraisons
- `shipment_tracking_logs` — Historique GPS

#### Bloc 7 : Wallet & Transactions
- `wallets` — Porte-monnaie
- `wallet_transactions` — Transactions (double-entry)
- `wallet_topups` — Dépôts
- `wallet_reservations` — Réservations
- `wallet_balance_history` — Audit trail

#### Bloc 8 : Paiements
- `user_payment_methods` — Méthodes paiement
- `payment_transactions` — Transactions
- `payment_disputes` — Litiges

#### Bloc 9 : Prescriptions & Médical
- `prescriptions` — Ordonnances
- `medical_records` — Dossier médical
- `medical_record_access` — Partage accès
- `consultations` — Consultations médicales

#### Bloc 10 : Blog & Contenu
- `blog_categories` — Catégories de blog
- `blog_posts` — Articles
- `blog_tags` — Tags
- `blog_post_tags` — Pivot article↔tag
- `blog_comments` — Commentaires imbriqués

#### Bloc 11 : Publicité
- `ad_campaigns` — Campagnes publicitaires
- `ad_placements` — Emplacements
- `ad_campaign_placements` — Pivot campagne↔emplacement
- `ad_impressions` — Impressions
- `ad_clicks` — Clics

#### Bloc 12 : API Tierces Parties
- `api_clients` — Clients API
- `api_client_permissions` — Permissions API
- `api_webhooks` — Webhooks sortants
- `api_webhook_logs` — Historique webhook

#### Bloc 13 : Internationalisation
- `languages` — Langues supportées
- `translations` — Clés de traduction

### Caractéristiques BDD

| Aspect | Valeur |
|--------|--------|
| **Engine** | InnoDB |
| **Encoding** | UTF-8 MB4 (support emoji) |
| **Transactions** | ACID complètes |
| **Backups** | Quotidiens (incremental) |
| **Replication** | Master-Slave (HA futur) |

### Optimisation

✅ Indexes sur colonnes recherchées fréquemment  
✅ FULLTEXT index sur `products.name` & `description`  
✅ Partitioning sur `wallet_transactions` (par date)  
✅ Archiving ancien data sales (~1an)  
✅ Query caching via Redis  

---

## Déploiement & Infrastructure

### Development

**Environnement** : PHP 8.1+, MySQL 8, Redis (local ou Laragon)

```bash
# Démarrer le serveur PHP
php -S localhost:8000

# Ou via Laragon (VirtualHost)
# http://afiazone.local
```

**Installation locale** :
```bash
# 1. Cloner le repo
git clone <repo> afiazone
cd afiazone

# 2. Installer dépendances Composer
composer install

# 3. Initialiser BDD
php bin/setup-db.php

# 4. Accéder au site
# http://localhost/index.php
```

### Production

**Hosting** : VPS ou Shared Hosting avec PHP 8.1+

**Prérequis** :
- PHP 8.1+ (avec extensions: PDO, json, redis, openssl)
- MySQL 8.0+
- Redis (optionnel mais recommandé)
- Composer

**Architecture** :
```
Nginx/Apache Reverse Proxy
        ↓
PHP App Server(s)
        ↓
MySQL Database
Redis Cache
```

**Configuration** :
- Rewrite rules pour router tout sur `index.php`
- PHP-FPM pour performance
- SSL/TLS obligatoire

**CI/CD** : GitHub Actions / GitLab CI
- Tests automatiques (PHPUnit)
- Linting (PHP CS Fixer, Psalm)
- Deploy via Git push ou FTP

**Monitoring** :
- Logs fichier (`/var/log/` ou `/logs/`)
- Sentry (error tracking, optionnel)
- Datadog / New Relic (APM, optionnel)

**Backup & Recovery** :
- Daily backups mysqldump
- S3 versioning
- RTO Max : 2 heures
- RPO Max : 1 heure

---

## Conventions & Standards

### Coding Standards

- **PSR-12** : PHP Code Style Guide
- **PHP CS Fixer** : Auto-formatting
- **Psalm** : Static analysis
- **PHPUnit** : Unit & Feature tests

### Naming Conventions

| Element | Convention | Exemple |
|---------|-----------|---------|
| Controllers | CamelCase + Controller | `ProductController` |
| Models | CamelCase (singular) | `Product`, `Order` |
| Services | CamelCase + Service | `WalletService` |
| Tables | snake_case (plural) | `products`, `user_roles` |
| Columns | snake_case | `user_id`, `created_at` |
| Methods | camelCase | `processPayment()` |
| Constants | UPPER_SNAKE_CASE | `MAX_UPLOAD_SIZE` |
| Routes | kebab-case | `/api/v1/products/search` |

### Git Workflow

```
main (production)
├── develop (integration)
│   └── feature/* (branches)
│       ├── feature/product-search
│       ├── feature/wallet-topup
│       └── bugfix/payment-validation
```

---

## Roadmap Phases

| Phase | Focus | Durée |
|-------|-------|-------|
| **A** | Préparation & specs | 1-2 sem |
| **B** | Architecture & BDD | 1 sem |
| **C** | Infrastructure & Boilerplate | 2 sem |
| **D** | Authentification & Autorisations | 2 sem |
| **E** | Module Utilisateurs & KYC | 2 sem |
| **F** | Catalogue Produits | 2 sem |
| **G** | Panier & Commandes | 2 sem |
| **H** | Livraison & Livreurs | 2 sem |
| **I** | Wallet Santé | 2 sem |
| **J** | Prescriptions & Dossier Médical | 2 sem |
| **K** | Paiements & Mobile Money | 2 sem |
| **L** | Administration & Modération | 1 sem |
| **M** | Statistiques & Analytics | 1 sem |
| **N** | Notifications & Emails | 1 sem |
| **O** | Sécurité & Conformité | 1 sem |
| **P** | Tests & QA | 2 sem |
| **Q** | Déploiement & Monitoring | 1 sem |
| **R** | Post-lancement & Optimisation | Ongoing |

**Total estimé** : 16–20 semaines

---

## Documents connexes

- [📋 Plan de Développement Complet](PLAN-COMPLET.md) — Spécifications détaillées par phase
- [📌 Plan rapide](plan.md) — Vue résumée

---

**Dernière mise à jour** : Mars 2026  
**Mainteneur** : équipe dev @AfiaZone
