# 📐 Architecture — AfiaZone

**Medical Marketplace avec E-Wallet Santé**
**Version** : 1.0-Beta
**Date** : Mars 2026 (Mise à jour dernière : 13 mars 2026)
**Stack** : PHP 8.1+ (MVC Custom) | MySQL 8 | Redis/File-based Caching
**Statut** : Phase D complétée (Authentification ✅)

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

## 📊 Statut de progression du projet

| Phase | Module | Statut | Implémentation |
|-------|--------|--------|-----------------|
| **D** | Authentification & Autorisation | ✅ **COMPLÉTÉ** | AuthController, AuthService, AuthMiddleware, RbacMiddleware |
| **E** | Utilisateurs & KYC | 🟡 **EN COURS** | UserController, KycController, KycService (models prêts) |
| **F** | Catalogue Produits | 🟡 **EN COURS** | ProductController, ProductService, ProductRepository (models prêts) |
| **G** | Panier & Commandes | 🟡 **EN COURS** | CartController, OrderController, CartService, OrderService |
| **H** | Livraison | 🟠 **PLANS** | DeliveryService (modèles prêts, intégration à faire) |
| **I** | E-Wallet Santé | 🟠 **PLANS** | WalletController, WalletService, WalletRepository |
| **J** | Prescriptions & Medical | 🟠 **PLANS** | Models créés (Prescription, MedicalRecord) |
| **K** | Paiements & Mobile Money | 🟠 **PLANS** | PaymentService (structure de base) |
| **L-O** | Admin, Analytics, Notifications, Sécurité | ⚫ **À FAIRE** | Partiellement implémentés (Admin, Logging) |

**Légende** : ✅ Complété | 🟡 En cours | 🟠 Planifié | ⚫ À faire

---

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

---

### 🚀 Quick Start - État actuel (13 mars 2026)

**Qu'est-ce qui marche** :
- ✅ Authentification JWT/Session (login, register, logout)
- ✅ RBAC (Role-Based Access Control)
- ✅ Middleware de sécurité (Auth, RBAC, CORS, Rate Limit, Logging)
- ✅ Modèles & bases de données pour tous les modules
- ✅ Structure de base pour UserController, ProductController, CartController, etc.
- ✅ Exception handling & request validation

**Comment tester** :
```bash
# 1. Démarrer le serveur
php -S localhost:8000

# 2. Tester authentification
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 3. Utiliser le JWT retourné pour les requêtes sécurisées
curl -H "Authorization: Bearer <JWT_TOKEN>" http://localhost:8000/api/user/profile
```

**Qu'il faut faire nextpour débloquer les phases suivantes** :
- [ ] Terminer endpoints KYC (Phase E)
- [ ] Terminer endpoints Produits avec search (Phase F)
- [ ] Intégrer panier & commandes (Phase G)
- [ ] Ajouter endpoints livraison (Phase H)

---

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
├── /back               # Pages backend(admin, marchant et partenaire)
├── /front              # Pages fontend(frontend et client)

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

## Modules métier

### ✅ 1. Module Authentification & Autorisation (Phase D) — COMPLÉTÉ

**Statut** : Complètement implémenté
**Fichiers** :
- `AuthController.php` & `AdminAuthController.php`
- `AuthService.php`
- `AuthMiddleware.php` & `RbacMiddleware.php`
- Models: `User.php`, `Role.php`, `Permission.php`, `Token.php`
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

### 🟡 2. Module Utilisateurs & KYC (Phase E) — EN COURS

**Statut** : Implémentation en cours
**Fichiers implémentés** :
- `UserController.php` & `KycController.php`
- `KycService.php`
- Models: `UserProfile.php`, `KycSubmission.php`, `KycDocument.php`, `Merchant.php`, `MerchantStock.php`
- `UserValidator.php`

**À faire** :
- Endpoints KYC complets
- Workflow approbation KYC
- Gestion tiers marchands

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

### 🟡 3. Module Catalogue Produits (Phase F) — EN COURS

**Statut** : Implémentation en cours
**Fichiers implémentés** :
- `ProductController.php`
- `ProductService.php` & `ProductRepository.php`
- Models: `Product.php`, `ProductCategory.php`, `ProductImage.php`, `ProductVariant.php`, `ProductAttribute.php`, `ProductReview.php`
- `ProductValidator.php`

**À faire** :
- Endpoints CRUD complets
- Recherche FULLTEXT
- Gestion variants & attributs
- Images produits (S3)

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

### 🟡 4. Module Panier & Commandes (Phase G) — EN COURS

**Statut** : Implémentation en cours
**Fichiers implémentés** :
- `CartController.php` & `OrderController.php`
- `CartService.php` & `OrderService.php`
- `OrderRepository.php`
- Models: `ShoppingCart.php`, `CartItem.php`, `Order.php`, `OrderItem.php`, `OrderStatusLog.php`
- `OrderValidator.php`

**À faire** :
- Endpoints panier complets
- Création commandes
- Statuts & workflow
- Remises & coupons

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

### 🟠 5. Module Livraison (Phase H) — PLANIFIÉ

**Statut** : Modèles créés, implémentation à planifier
**Fichiers implémentés** :
- `DeliveryService.php`
- Models: `DeliveryPersonnel.php`, `Shipment.php`, `ShipmentTrackingLog.php`

**À faire** :
- Controller & endpoints livraison
- Integration tracking GPS
- Statuts & workflow
- Confirmation livraison

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

### 🟠 6. Module E-Wallet Santé (Phase I) — PLANIFIÉ

**Statut** : Modèles & services de base créés
**Fichiers implémentés** :
- `WalletController.php`
- `WalletService.php` & `WalletRepository.php`
- Models: `Wallet.php`, `WalletTransaction.php`, `WalletReservation.php`
- `WalletValidator.php`

**À faire** :
- Endpoints wallet complets
- Top-up (card/mobile money)
- Réservations & débits
- Historique & audit

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

### 🟠 7. Module Prescriptions & Dossier Médical (Phase J) — PLANIFIÉ

**Statut** : Modèles créés, implémentation à planifier
**Fichiers implémentés** :
- Models: `Prescription.php`, `MedicalRecord.php`

**À faire** :
- Controller & Service
- Upload & vérification prescriptions
- EMR (dossier médical)
- Partage d'accès & consultations

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

### 🟠 8. Module Paiements & Mobile Money (Phase K) — PLANIFIÉ

**Statut** : Service de base créé, implémentation à planifier
**Fichiers implémentés** :
- `PaymentService.php`
- Models: `PaymentTransaction.php`

**À faire** :
- Controller & endpoints paiement
- Intégration passerelles
- Mobile money (Airtel, Vodacom, Orange)
- Refunds & disputes

---

### ⚫ 9. Module Administration & Modération (Phase L) — À FAIRE

**Statut** : Controller admin créé, fonctionnalités à développer
**Fichiers implémentés** :
- `AdminAuthController.php` & `AdminDashboardController.php`
- `LoggingMiddleware.php`

**À faire** :
- Dashboard admin complet
- Gestion utilisateurs & marchands
- Modération contenu
- Paramètres système

---

### ⚫ 10-11. Analytics, Notifications, Emails (Phases M-N) — À FAIRE

**À faire** :
- Controllers & Services pour analytics
- Notifications en temps réel (WebSocket/SSE)
- Système queue asynchrone
- Emails transactionnels

---

### ⚫ 12-16. Blog, Ads, API Tierces, i18n, Autres (Phases S-V) — À FAIRE

**À faire** :
- Module Blog & CMS
- Système de publicités in-app
- API tierces & webhooks
- Internationalisation (i18n)
- Autres modules futurs

---

## État des implémentations techniques

### ✅ Implémentés

**Controllers** (9 fichiers)
- `AuthController.php` — Authentification complète
- `AdminAuthController.php` — Auth admin
- `UserController.php` — Gestion utilisateurs
- `KycController.php` — KYC
- `ProductController.php` — Produits
- `CartController.php` — Panier
- `OrderController.php` — Commandes
- `WalletController.php` — Wallet
- `HealthController.php` — Health check

**Services** (9 fichiers)
- `AuthService.php` — Auth complet
- `UserService.php` — Profils users
- `KycService.php` — KYC workflow
- `ProductService.php` — Produits
- `CartService.php` — Panier
- `OrderService.php` — Commandes
- `WalletService.php` — Wallet
- `PaymentService.php` — Base paiements
- `DeliveryService.php` — Base livraison

**Models** (31 fichiers)
- Auth: `User.php`, `Role.php`, `Permission.php`, `Token.php`
- Users: `UserProfile.php`
- KYC: `KycSubmission.php`, `KycDocument.php`, `Merchant.php`, `MerchantStock.php`
- Products: `Product.php`, `ProductCategory.php`, `ProductImage.php`, `ProductVariant.php`, `ProductAttribute.php`, `ProductReview.php`
- Cart/Order: `ShoppingCart.php`, `CartItem.php`, `Order.php`, `OrderItem.php`, `OrderStatusLog.php`
- Wallet: `Wallet.php`, `WalletTransaction.php`, `WalletReservation.php`
- Delivery: `DeliveryPersonnel.php`, `Shipment.php`, `ShipmentTrackingLog.php`
- Payments: `PaymentTransaction.php`
- Medical: `Prescription.php`, `MedicalRecord.php`
- Notifications: `Notification.php`

**Repositories** (5 fichiers)
- `UserRepository.php` — Users
- `ProductRepository.php` — Products
- `OrderRepository.php` — Orders
- `WalletRepository.php` — Wallets
- `BaseRepository.php` — Base class

**Validators** (5 fichiers)
- `UserValidator.php`
- `ProductValidator.php`
- `OrderValidator.php`
- `WalletValidator.php`
- `Validator.php` — Base class

**Middleware** (7 fichiers)
- `AuthMiddleware.php` — JWT/Session validation
- `RbacMiddleware.php` — Permission checking
- `RateLimitMiddleware.php` — Rate limiting
- `CorsMiddleware.php` — CORS policy
- `LoggingMiddleware.php` — Audit logging
- `VerifiedMiddleware.php` — Email verification
- `Middleware.php` — Base class

**Exceptions** (6 fichiers)
- `HttpException.php`, `ValidationException.php`, `UnauthorizedException.php`, `ForbiddenException.php`, `NotFoundException.php`, `Exceptions.php`

**Console** (4 fichiers)
- `MigrateCommand.php`, `RollbackCommand.php`, `SeedCommand.php`, `Command.php`

---

### 🟠 En préparation / À faire

**Futures implémentations** :
- Blog & CMS (`BlogController`, `BlogService`, `BlogModel`)
- Publicité in-app (`AdController`, `AdService`)
- API Tierces Parties & Webhooks
- Internationalisation (i18n/Locales)
- Analytics & Dashboards (`AnalyticsService`)
- Notifications & Emails (`NotificationService`)
- S3 Upload Manager pour fichiers

---

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

| Phase | Focus | Statut | Fin estimée |
|-------|-------|--------|-------------|
| **A** | Préparation & specs | ✅ Fait | - |
| **B** | Architecture & BDD | ✅ Fait | - |
| **C** | Infrastructure & Boilerplate | ✅ Fait (Laragon) | - |
| **D** | Authentification & Autorisations | ✅ **COMPLÉTÉ** | 13 mars 2026 |
| **E** | Module Utilisateurs & KYC | 🟡 En cours | TBD |
| **F** | Catalogue Produits | 🟡 En cours | TBD |
| **G** | Panier & Commandes | 🟡 En cours | TBD |
| **H** | Livraison & Livreurs | 🟠 Planifié | TBD |
| **I** | Wallet Santé | 🟠 Planifié | TBD |
| **J** | Prescriptions & Dossier Médical | 🟠 Planifié | TBD |
| **K** | Paiements & Mobile Money | 🟠 Planifié | TBD |
| **L** | Administration & Modération | ⚫ À faire | TBD |
| **M** | Statistiques & Analytics | ⚫ À faire | TBD |
| **N** | Notifications & Emails | ⚫ À faire | TBD |
| **O** | Sécurité & Conformité | ⚫ À faire | TBD |
| **P** | Tests & QA | ⚫ À faire | TBD |
| **Q** | Déploiement & Monitoring | ⚫ À faire | TBD |
| **R** | Post-lancement & Optimisation | ⚫ À faire | Ongoing |
| **S-V** | Blog, Ads, API, i18n, Extras | ⚫ Futures phases | TBD |

**Progression globale** : Phase D/16 complétée (6% du plan initial) | Infrastructure & Auth solides

**Prochaines étapes prioritaires** :
1. Terminer Phase E (KYC)
2. Terminer Phase F (Produits)
3. Terminer Phase G (Commandes)

---

## Documents connexes

- [📋 Plan de Développement Complet](PLAN-COMPLET.md) — Spécifications détaillées par phase
- [📌 Plan rapide](plan.md) — Vue résumée

---

**Dernière mise à jour** : 13 mars 2026 (14:30)
**Mainteneur** : équipe dev @AfiaZone
**Branch** : main | **Commit** : 132f76c (Auth systeme finish)
