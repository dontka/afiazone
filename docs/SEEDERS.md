# Seeders — AfiaZone

## Vue d'ensemble

Le système de seeders permet de peupler la base de données avec des données de développement ou de référence. Chaque seeder est un fichier PHP dans `database/seeders/` qui retourne un tableau associatif `['run' => fn(PDO)]`.

Les seeders sont exécutés dans l'ordre de leur préfixe numérique, respectant les dépendances de clés étrangères.

## Commandes

```bash
# Exécuter tous les seeders
php bin/seed.php

# Exécuter un seeder spécifique
php bin/seed.php users

# Exécuter plusieurs seeders
php bin/seed.php users,products,orders

# Lister les seeders disponibles
php bin/seed.php --list

# Drop tout + re-migrer + seeder (destructif)
php bin/seed.php --fresh
```

## Format d'un fichier seeder

```php
<?php

declare(strict_types=1);

return [
    'run' => function (\PDO $pdo): void {
        $pdo->exec("
            INSERT INTO example (name) VALUES ('Donnée de test')
            ON DUPLICATE KEY UPDATE name=name
        ");
    },
];
```

## Seeders existants

| # | Clé | Fichier | Données |
|---|-----|---------|---------|
| 000 | `roles` | `000_RolesPermissionsSeeder.php` | 6 rôles, 31 permissions, 65 associations rôle-permission |
| 001 | `users` | `001_UsersSeeder.php` | 14 utilisateurs : 1 admin, 1 modérateur, 3 marchands, 5 clients, 3 livreurs, 1 partenaire |
| 002 | `merchants` | `002_MerchantsSeeder.php` | 4 tiers, 3 profils marchand, infos livraison, frais |
| 003 | `products` | `003_ProductsSeeder.php` | 5 catégories, 25 produits pharma/médical, images, 4 variantes, stocks |
| 004 | `orders` | `004_OrdersSeeder.php` | 20 commandes, ~53 articles, logs de statut, adresses, 2 paniers actifs |
| 005 | `wallet` | `005_WalletSeeder.php` | 14 portefeuilles, ~72 transactions, ~47 rechargements, historique soldes |
| 006 | `delivery` | `006_DeliverySeeder.php` | 3 prestataires, 3 livreurs, expéditions, logs de suivi |
| 007 | `reviews` | `007_ReviewsSeeder.php` | Avis produits (~26), avis marchands (~11), avis livreurs (~7) |
| 008 | `blog` | `008_BlogSeeder.php` | 5 catégories, 6 articles santé, 10 tags, ~16 commentaires |
| 009 | `notifications` | `009_NotificationsSeeder.php` | ~34 notifications, 3 langues, 6 placements pub, 5 codes promo, 4 tickets support |

## Comptes utilisateurs de test

| Email | Rôle | Mot de passe |
|-------|------|-------------|
| `admin@afiazone.com` | admin | `Password123!` |
| `moderator@afiazone.com` | moderator | `Password123!` |
| `pharma1@afiazone.com` | merchant | `Password123!` |
| `pharma2@afiazone.com` | merchant | `Password123!` |
| `pharma3@afiazone.com` | merchant | `Password123!` |
| `client1@example.com` | customer | `Password123!` |
| `client2@example.com` | customer | `Password123!` |
| `client3@example.com` | customer | `Password123!` |
| `client4@example.com` | customer | `Password123!` |
| `client5@example.com` | customer | `Password123!` |
| `livreur1@afiazone.com` | deliverer | `Password123!` |
| `livreur2@afiazone.com` | deliverer | `Password123!` |
| `livreur3@afiazone.com` | deliverer | `Password123!` |
| `partner1@example.com` | partner | `Password123!` |

## Comment tester la connexion par rôle

| Rôle | Où se connecter | URL / Endpoint | Résultat |
|------|------------------|----------------|----------|
| `admin`, `moderator` | Panel admin (HTML) | `GET/POST /admin/login` | Cookie `auth_token` + accès dashboard |
| `merchant` | Panel admin (HTML) ou Auth publique (API/front) | `/admin/login` ou `POST /auth/login` | Redirect `/admin/dashboard/merchant` (admin login) ou JWT (`data.token`) |
| `partner` | Panel admin (HTML) ou Auth publique (API/front) | `/admin/login` ou `POST /auth/login` | Redirect `/admin/dashboard/partner` (admin login) ou JWT (`data.token`) |
| `deliverer` | Panel admin (HTML) ou Auth publique (API/front) | `/admin/login` ou `POST /auth/login` | Redirect `/admin/dashboard/deliverer` (admin login) ou JWT (`data.token`) |
| `customer` | Auth publique (API/front) | `POST /auth/login` ou page `GET /auth/login` | JWT (`data.token`) + utilisateur (`data.user`) |

Notes :

- `customer` n'a pas accès au panel `/admin/*`.
- `merchant`, `partner`, `deliverer` ont un dashboard dédié côté `/admin/dashboard/*`.
- Pour les détails complets auth/JWT/middlewares, voir `docs/AUTH.md`.

## Ordre d'exécution et dépendances

```
roles → users → merchants → products → orders → wallet → delivery → reviews → blog → notifications
```

Chaque seeder dépend des données créées par les seeders précédents. Exécuter un seeder isolé suppose que ses dépendances sont déjà présentes en base.

## Fonctionnement interne

- `FOREIGN_KEY_CHECKS` est désactivé pendant l'exécution pour éviter les erreurs d'ordre.
- Les seeders utilisent `INSERT ... ON DUPLICATE KEY UPDATE` ou `INSERT IGNORE` pour être **idempotents** (ré-exécutables sans erreur).
- Le runner se connecte via le helper `db()` ou en fallback direct via les variables d'environnement.
- L'option `--fresh` appelle `bin/migrate.php fresh` avant d'exécuter les seeders.

## Bonnes pratiques

1. **Idempotence** — Utiliser `ON DUPLICATE KEY UPDATE` ou `INSERT IGNORE` pour que le seeder soit ré-exécutable.
2. **Données réalistes** — Générer des données proches de la production (noms, adresses, montants).
3. **Ordre des dépendances** — Préfixer numériquement les fichiers pour respecter les FK.
4. **Ne pas utiliser en production** — Les seeders sont destinés au développement et aux tests uniquement.
5. **Tester avec `--fresh`** — Valider la chaîne complète migrations + seeders avant de pousser.
