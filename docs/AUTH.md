# PHASE D – Authentification & Autorisations

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Architecture JWT](#2-architecture-jwt)
3. [Endpoints Auth API (public)](#3-endpoints-auth-api-public)
4. [Endpoints Admin (panel)](#4-endpoints-admin-panel)
5. [Middlewares](#5-middlewares)
6. [Syntaxe des routes protégées](#6-syntaxe-des-routes-protégées)
7. [RBAC – Rôles & Permissions](#7-rbac--rôles--permissions)
8. [Service AuthService](#8-service-authservice)
9. [Tokens de vérification (table `tokens`)](#9-tokens-de-vérification-table-tokens)
10. [Configuration Email](#10-configuration-email)
11. [Sécurité](#11-sécurité)
12. [Comptes de test](#12-comptes-de-test)

---

## 1. Vue d'ensemble

AfiaZone utilise un système d'authentification **JWT** (JSON Web Tokens) avec deux canaux distincts :

| Canal | Cible | Transport |
|-------|-------|-----------|
| **API publique** | Clients mobiles / SPA | Header `Authorization: Bearer <token>` |
| **Admin panel** | Navigateur web | Cookie `auth_token` (HttpOnly) |

Le flow général est le suivant :

```
POST /auth/login (ou /admin/login)
       ↓
AuthService::login()
  → vérifie credentials (email + bcrypt)
  → génère JWT signé HS256
       ↓
API       → retourne JSON { token, user }
Web/API   → set-cookie auth_token + JWT JSON (selon endpoint)
Admin     → redirection par rôle (/admin/dashboard, /admin/dashboard/merchant, /admin/dashboard/partner, /admin/dashboard/deliverer)
       ↓
Routes protégées
  → AuthMiddleware valide le token (Bearer OU cookie)
  → RbacMiddleware vérifie le rôle requis
  → $GLOBALS['auth_user'] accessible dans les contrôleurs
```

---

## 2. Architecture JWT

### Structure du payload

```json
{
  "iss": "https://afiazone.test",
  "sub": 42,
  "email": "admin@afiazone.com",
  "roles": ["admin"],
  "iat": 1700000000,
  "exp": 1700003600
}
```

| Claim | Type | Description |
|-------|------|-------------|
| `iss` | string | Issuer — valeur `APP_URL` |
| `sub` | int | ID de l'utilisateur (PK `users.id`) |
| `email` | string | Email de l'utilisateur |
| `roles` | string[] | Rôles assignés |
| `iat` | int | Timestamp d'émission |
| `exp` | int | Timestamp d'expiration |

### Configuration `.env`

```dotenv
JWT_SECRET=<clé-secrète-forte>
JWT_EXPIRATION=3600          # durée access token (secondes), défaut 1h
JWT_REFRESH_EXPIRATION=604800 # durée refresh token, défaut 7j
```

> **Note :** Si `JWT_SECRET` est vide, la valeur `APP_KEY` est utilisée en fallback.

### Algorithme

- Bibliothèque : `firebase/php-jwt`
- Algorithme : `HS256`
- Classe : `App\Services\AuthService` → `generateJwt(User $user): string`

---

## 3. Endpoints Auth API (public)

### Inscription

```
POST /auth/register
Content-Type: application/json
```

**Corps :**
```json
{
  "email": "user@example.com",
  "password": "minuit2024!",
  "first_name": "Marie",
  "last_name": "Curie",
  "phone": "+33612345678"
}
```

**Réponse 201 :**
```json
{
  "user": { "id": 10, "email": "...", "roles": ["customer"], ... },
  "token": "<JWT>"
}
```

- Valide : `email` (unique), `password` (min 8 car.), `first_name`, `last_name`
- Crée l'utilisateur avec `status = 'pending_verification'`
- Assigne le rôle `customer`
- Envoie un email de vérification
- Retourne immédiatement un JWT (accès limité jusqu'à vérification)

---

### Connexion

```
POST /auth/login
Content-Type: application/json
```

**Corps :**
```json
{
  "email": "user@example.com",
  "password": "minuit2024!"
}
```

**Réponse 200 :**
```json
{
  "user": { "id": 10, "roles": ["customer"], ... },
  "token": "<JWT>",
  "email_verified": true
}
```

- Lève `UnauthorizedException` → 401 si credentials invalides
- Lève `ForbiddenException` → 403 si compte `banned`
- Met à jour `last_login_at`
- Définit aussi un cookie HttpOnly `auth_token` (en plus du JSON) pour les pages web protégées

---

### Déconnexion

```
POST /auth/logout
Authorization: Bearer <token>
```

**Réponse 200 :**
```json
{ "message": "Logout successful" }
```

- Middleware `auth` requis
- Ajoute le hash SHA-256 du token dans la table `tokens` avec `is_used = true` (blacklist)

---

### Rafraîchissement du token

```
POST /auth/refresh
Content-Type: application/json
```

**Corps :**
```json
{ "token": "<JWT-expiré-ou-valide>" }
```

**Réponse 200 :**
```json
{ "token": "<nouveau-JWT>" }
```

---

### Vérification email

```
GET  /auth/verify-email?token=<plain-token>
POST /auth/verify-email
     { "token": "<plain-token>" }
```

- Met `email_verified_at` et `status = 'active'`
- Marque le token comme utilisé (`is_used = true`)

---

### Mot de passe oublié

```
POST /auth/forgot-password
Content-Type: application/json

{ "email": "user@example.com" }
```

**Réponse 200 :** (toujours le même message pour éviter l'énumération d'emails)
```json
{ "message": "If the email exists, a reset link was sent" }
```

- Durée du token de reset : **30 minutes** (1800 s)

---

### Réinitialisation du mot de passe

```
POST /auth/reset-password
Content-Type: application/json

{
  "token": "<plain-token>",
  "password": "nouveau-motdepasse!"
}
```

**Réponse 200 :**
```json
{ "message": "Password reset successfully" }
```

---

## 4. Endpoints Admin (panel)

Ces routes retournent des pages HTML et gèrent les redirections.

| Méthode | Path | Action |
|---------|------|--------|
| `GET` | `/admin` | Redirect → `/admin/login` |
| `GET` | `/admin/login` | Affiche la page de connexion |
| `POST` | `/admin/login` | Authentifie, set cookie, redirect `/admin/dashboard` |
| `GET` | `/admin/register` | Affiche la page d'inscription |
| `POST` | `/admin/register` | Crée un compte admin |
| `GET` | `/admin/forgot-password` | Affiche le formulaire mot de passe oublié |
| `POST` | `/admin/forgot-password` | Envoie l'email de reset |
| `GET` | `/admin/reset-password` | Affiche le formulaire reset (paramètre `?token=`) |
| `POST` | `/admin/reset-password` | Applique le nouveau mot de passe |
| `GET` | `/admin/2fa` | Affiche la page 2FA |
| `POST` | `/admin/logout` | Déconnecte, supprime le cookie |
| `GET` | `/admin/dashboard` | Dashboard admin/moderator (`rbac:super_admin,admin,moderator`) |
| `GET` | `/admin/dashboard/merchant` | Dashboard merchant (`rbac:super_admin,admin,merchant`) |
| `GET` | `/admin/dashboard/partner` | Dashboard partner (`rbac:super_admin,admin,partner`) |
| `GET` | `/admin/dashboard/deliverer` | Dashboard deliverer (`rbac:super_admin,admin,deliverer`) |

### Champ du formulaire de connexion

Le champ email accepte les deux noms : `email-username` **ou** `email`.

```html
<input name="email-username" type="email" />
<input name="password" type="password" />
```

### Cookie auth_token

```php
setcookie('auth_token', $jwt, [
    'expires'  => time() + $ttl,
    'path'     => '/',
    'httponly' => true,
    'secure'   => $isHttps,   // true uniquement si HTTPS réel
    'samesite' => 'Lax',
]);
```

La valeur `secure` est détectée dynamiquement :

```php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
```

> **Important :** Ne pas utiliser `APP_ENV === 'production'` pour ce flag — cela provoquerait le rejet du cookie en HTTP local même avec `APP_ENV=development`.

### Contrôle d'accès admin

Lors du login admin, le contrôleur vérifie que l'utilisateur possède au moins un des rôles :

```php
array_intersect($roles, ['super_admin', 'admin', 'moderator', 'merchant', 'partner', 'deliverer'])
```

Un utilisateur `customer` ne peut pas accéder au panel admin.

---

## 5. Middlewares

### AuthMiddleware (`app/Middleware/AuthMiddleware.php`)

Valide le JWT pour toutes les routes protégées par `'auth'`.

**Algorithme :**

```
1. Chercher Bearer token dans Authorization header
2. Si absent → chercher cookie auth_token
3. Si toujours absent → redirect /admin/login (routes admin) ou 401 JSON
4. Vérifier blacklist (isTokenBlacklisted)
5. Décoder et valider le JWT
6. Charger User depuis DB (vérifier status ≠ 'banned')
7. Stocker dans $GLOBALS['auth_user'], ['auth_payload'], ['auth_token']
```

**Redirections vs JSON :**

| Type de route | Token absent/invalide | Résultat |
|--------------|----------------------|---------|
| `/admin/*` | oui | `Location: /admin/login` + exit |
| Toute autre route | oui | HTTP 401 JSON |

---

### RbacMiddleware (`app/Middleware/RbacMiddleware.php`)

Vérifie les rôles et/ou permissions après `AuthMiddleware`.

```php
class RbacMiddleware extends Middleware
{
    public function __construct(
        private array $requiredRoles = [],
        private array $requiredPermissions = []
    ) {}
}
```

**Algorithme :**

```
1. Lire $GLOBALS['auth_user']
2. Si absent → redirect /admin/login (admin) ou 401
3. Vérifier intersection entre rôles user et $requiredRoles
4. Vérifier chaque permission de $requiredPermissions
5. Retourner true (pass) ou abort 403
```

---

## 6. Syntaxe des routes protégées

Les middlewares sont définis dans `routes/api.php` via la clé `'middleware'` :

```php
[
    'method' => 'GET',
    'path' => '/admin/dashboard',
    'controller' => 'Admin\DashboardController@dashboard',
    'middleware' => ['auth', 'rbac:super_admin,admin,moderator'],
]
```

### Syntaxe `rbac:role1,role2`

Le dispatcher dans `public/index.php` parse automatiquement les paramètres :

```php
if (str_contains($middlewareEntry, ':')) {
    [$middlewareName, $paramStr] = explode(':', $middlewareEntry, 2);
    $params = explode(',', $paramStr);
    // → new RbacMiddleware(['super_admin', 'admin', 'moderator'])
}
```

### Middlewares disponibles

| Identifiant | Classe | Paramètres |
|-------------|--------|-----------|
| `auth` | `AuthMiddleware` | aucun |
| `rbac:role1,role2` | `RbacMiddleware` | rôles requis |
| `verified` | `VerifiedMiddleware` | aucun |

### Exemples de routes

```php
// Authentifié uniquement
'middleware' => ['auth']

// Authentifié + email vérifié
'middleware' => ['auth', 'verified']

// Admin/Modérateur uniquement
'middleware' => ['auth', 'rbac:super_admin,admin,moderator']

// Super admin seulement
'middleware' => ['auth', 'rbac:super_admin']
```

---

## 7. RBAC – Rôles & Permissions

### Rôles

| Rôle | Description | Accès admin panel |
|------|-------------|:-----------------:|
| `super_admin` | Accès complet, toutes permissions | ✅ |
| `admin` | Administrateur, toutes permissions | ✅ |
| `moderator` | Modération de contenu | ✅ |
| `merchant` | Vendeur sur la marketplace | ✅ (dashboard dédié) |
| `customer` | Client régulier | ❌ |
| `deliverer` | Livreur | ✅ (dashboard dédié) |
| `partner` | Organisation partenaire | ✅ (dashboard dédié) |

### Permissions (31 au total)

| Permission | Rôles |
|-----------|-------|
| `manage_users` | admin, moderator |
| `manage_roles` | admin |
| `manage_products` | admin |
| `create_product` | admin, merchant |
| `update_product` | admin, merchant |
| `delete_product` | admin, merchant |
| `view_products` | tous |
| `manage_orders` | admin, moderator |
| `create_order` | admin, customer |
| `update_order` | admin, merchant |
| `view_orders` | admin, moderator, merchant, customer, deliverer |
| `cancel_order` | admin, customer |
| `manage_wallet` | admin |
| `view_wallet` | admin, merchant, customer, deliverer |
| `topup_wallet` | admin, merchant, customer, deliverer |
| `transfer_funds` | admin, merchant, customer, deliverer |
| `manage_kyc` | admin, moderator |
| `submit_kyc` | admin, merchant, customer, deliverer |
| `manage_merchants` | admin |
| `manage_deliveries` | admin |
| `view_analytics` | admin, moderator |
| `manage_prescriptions` | admin, moderator |
| `manage_reports` | admin, moderator |
| `manage_support` | admin, moderator |
| `manage_promotions` | admin |
| `manage_blog` | admin, moderator |
| `moderate_comments` | admin, moderator |
| `manage_ads` | admin, merchant |
| `manage_api_clients` | admin |
| `manage_translations` | admin |
| `manage_languages` | admin |

### Structure DB

```
roles               users               permissions
──────────          ─────────           ────────────
id                  id                  id
name                email               name
description         roles[] via…        description

user_roles                  role_permissions
───────────                 ────────────────
user_id → users.id          role_id → roles.id
role_id → roles.id          permission_id → permissions.id
```

### API User Model

```php
$user->getRoleNames();          // ['admin', 'moderator']
$user->hasRole('admin');        // true/false
$user->hasPermission('manage_users'); // true/false
$user->assignRole('customer');  // assigne un rôle
```

---

## 8. Service AuthService

Fichier : `app/Services/AuthService.php`

| Méthode | Signature | Description |
|---------|-----------|-------------|
| `register` | `(array $data): array` | Crée user, assigne rôle customer, envoie email vérif., retourne JWT |
| `login` | `(string $email, string $password): array` | Vérifie credentials, retourne JWT + user |
| `logout` | `(string $token): void` | Blackliste le token JWT dans `tokens` |
| `validateToken` | `(string $token): ?array` | Décode et valide, retourne payload ou null |
| `refreshToken` | `(string $currentToken): ?string` | Génère nouveau JWT à partir d'un token valide |
| `isTokenBlacklisted` | `(string $token): bool` | Vérifie si le hash SHA-256 existe dans la blacklist |
| `verifyEmail` | `(string $plainToken): bool` | Marque email comme vérifié |
| `resendVerificationEmail` | `(User $user): bool` | Renvoie l'email de vérification |
| `requestPasswordReset` | `(string $email): array` | Envoie lien de reset (30 min) |
| `resetPassword` | `(string $plainToken, string $newPassword): bool` | Applique le nouveau mot de passe |

---

## 9. Tokens de vérification (table `tokens`)

La table `tokens` sert à plusieurs usages :

| `token_type` | TTL | Usage |
|-------------|-----|-------|
| `email_verification` | 86400 s (24h) | Vérification d'adresse email |
| `password_reset` | 1800 s (30 min) | Réinitialisation de mot de passe |
| `jwt` | selon `exp` JWT | Blacklist des tokens révoqués (logout) |

### Stockage sécurisé

Les tokens sont stockés hashés (`sha256`) en base. La valeur plaintext n'est disponible qu'à la création :

```php
$tokenData = Token::createForUser($userId, 'email_verification', 86400);
// $tokenData['plain']  → envoyé dans l'email
// $tokenData['hash']   → stocké en DB
```

---

## 10. Configuration Email

Les emails transactionnels (vérification d'adresse, reset de mot de passe) sont envoyés via **Symfony Mailer** en utilisant les variables `.env` suivantes :

```dotenv
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@afiazone.test
MAIL_FROM_NAME=AfiaZone
```

Si `MAIL_DSN` est défini, il est utilisé directement ; sinon le DSN est construit depuis `MAIL_HOST` + `MAIL_PORT`.

### Configurations selon l'environnement

| Environnement | Service | `MAIL_HOST` | `MAIL_PORT` | Credentials |
|--------------|---------|-------------|-------------|-------------|
| **Local (Laragon)** | Mailpit | `localhost` | `1025` | aucun |
| **Local (Mailtrap)** | Mailtrap | `sandbox.smtp.mailtrap.io` | `2525` | oui |
| **Production** | SMTP réel (ex: Gmail, SES) | hôte SMTP | `465`/`587` | oui |
| **Production** | Mailgun / Postmark via DSN | — | — | `MAIL_DSN=...` |

### Exemples de configuration

**Mailpit (défaut Laragon — aucune config, web UI sur http://localhost:8025) :**
```dotenv
MAIL_HOST=localhost
MAIL_PORT=1025
```

**Mailtrap :**
```dotenv
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<username-mailtrap>
MAIL_PASSWORD=<password-mailtrap>
```

**Gmail (SMTP avec App Password) :**
```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-compte@gmail.com
MAIL_PASSWORD=<app-password-16-chars>
```

**Mailgun via DSN :**
```dotenv
MAIL_DSN=mailgun+https://KEY:DOMAIN@default
```

**Amazon SES via DSN :**
```dotenv
MAIL_DSN=ses+smtp://ACCESS_KEY:SECRET@default?region=eu-west-1
```

### Tester l'envoi

```bash
php bin/test-mail.php
```

Envoie un email de test à `admin@afiazone.com` avec le DSN courant.

---

## 11. Sécurité

### Mots de passe

- Hachage **bcrypt** via `password_hash($password, PASSWORD_BCRYPT)`
- Validation minimale : 8 caractères

### Protection contre l'énumération d'emails

`requestPasswordReset()` retourne toujours `['sent' => true]` même si l'email n'existe pas.

### Cookie HttpOnly

Le cookie `auth_token` est défini avec :
- `httponly: true` — inaccessible depuis JavaScript
- `secure: true` — uniquement si connexion HTTPS réelle (détectée via `$_SERVER['HTTPS']`)
- `samesite: Lax` — protection CSRF partielle

### Blacklist JWT

À la déconnexion, le hash SHA-256 du token est stocké en DB. `AuthMiddleware` vérifie cette blacklist à chaque requête.

### Statuts utilisateur

| `status` | Connexion autorisée | Description |
|----------|:------------------:|-------------|
| `pending_verification` | ✅ (accès limité) | Email pas encore vérifié |
| `active` | ✅ | Compte normal |
| `banned` | ❌ | Compte suspendu |

### Headers de sécurité recommandés

À ajouter dans la configuration du vhost ou dans `public/index.php` :

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Content-Security-Policy: default-src 'self'
```

---

## 12. Comptes de test

Créés par le seeder `001_UsersSeeder.php` :

| Email | Mot de passe | Rôle(s) | Accès panel admin |
|-------|-------------|---------|:-----------------:|
| `admin@afiazone.com` | `Password123!` | admin | ✅ |
| `moderator@afiazone.com` | `Password123!` | moderator | ✅ |
| `pharma1@afiazone.com` | `Password123!` | merchant | ✅ (dashboard merchant) |
| `client1@example.com` | `Password123!` | customer | ❌ |
| `deliverer@afiazone.com` | `Password123!` | deliverer | ✅ (dashboard deliverer) |
| `partner1@example.com` | `Password123!` | partner | ✅ (dashboard partner) |

### Se connecter selon le rôle

| Rôle | Canal recommandé | URL / Endpoint | Données à envoyer | Résultat attendu |
|------|------------------|----------------|-------------------|------------------|
| `super_admin`, `admin`, `moderator` | **Panel admin (HTML)** | `GET/POST /admin/login` | Form-data: `email-username` (ou `email`) + `password` | Cookie `auth_token` + redirect `/admin/dashboard` |
| `merchant` | **Panel admin (HTML)** ou **Auth publique** | `/admin/login` ou `/auth/login` | Form-data (admin) ou JSON (auth) | Redirect `/admin/dashboard/merchant` (si admin login) |
| `partner` | **Panel admin (HTML)** ou **Auth publique** | `/admin/login` ou `/auth/login` | Form-data (admin) ou JSON (auth) | Redirect `/admin/dashboard/partner` (si admin login) |
| `deliverer` | **Panel admin (HTML)** ou **Auth publique** | `/admin/login` ou `/auth/login` | Form-data (admin) ou JSON (auth) | Redirect `/admin/dashboard/deliverer` (si admin login) |
| `customer` | **Auth publique (API/front)** | `POST /auth/login` (ou page `GET /auth/login`) | JSON: `email`, `password` | JSON avec JWT (`data.token`) + profil (`data.user`) |

Notes importantes :

- `customer` qui tente `/admin/login` reçoit **Access denied**.
- `merchant`, `partner`, `deliverer` peuvent se connecter via `/admin/login` et sont redirigés vers leur dashboard dédié.
- Les rôles admin peuvent aussi utiliser `POST /auth/login` s'ils veulent un JWT pour l'API.
- Pour tester tous les comptes seedés (14 utilisateurs), voir aussi `docs/SEEDERS.md`.

### Exemples rapides

**A) Connexion admin/moderator (navigateur)**

1. Ouvrir `http://afiazone.test/admin/login`
2. Se connecter avec `admin@afiazone.com` / `Password123!` (ou `moderator@afiazone.com`)
3. Vérifier la redirection vers `/admin/dashboard`

**B) Connexion rôle non-admin (API)**

```bash
curl -X POST http://afiazone.test/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"client1@example.com","password":"Password123!"}'
```

Réponse attendue (enveloppe API) :

```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "token": "<JWT>",
    "user": { "email": "client1@example.com", "roles": ["customer"] }
  }
}
```

**C) Connexion livreur (dashboard dédié)**

1. Ouvrir `http://afiazone.test/admin/login`
2. Se connecter avec `livreur1@afiazone.com` / `Password123!`
3. Vérifier la redirection vers `/admin/dashboard/deliverer`

> Liste complète des 14 comptes seedés : `docs/SEEDERS.md`.

### Réinitialiser les comptes

```bash
php bin/seed.php --fresh
# ou pour les utilisateurs seuls :
php bin/seed.php 001
```

---

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Services/AuthService.php` | Toute la logique auth (JWT, tokens, emails) |
| `app/Controllers/AuthController.php` | API auth publique |
| `app/Controllers/Admin/AuthController.php` | Auth panel admin (HTML + cookies) |
| `app/Middleware/AuthMiddleware.php` | Validation JWT sur routes protégées |
| `app/Middleware/RbacMiddleware.php` | Contrôle d'accès par rôle |
| `app/Models/User.php` | Méthodes `getRoleNames()`, `hasPermission()` |
| `routes/api.php` | Définition des routes + middlewares |
| `database/seeders/000_RolesPermissionsSeeder.php` | Données de référence RBAC |
| `database/seeders/001_UsersSeeder.php` | Comptes de test |
