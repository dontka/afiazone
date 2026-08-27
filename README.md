# AfiaZone

AfiaZone est une marketplace intelligente de produits de sante construite en PHP natif, MySQL et architecture MVC modulaire sans framework applicatif.

## Demarrage local

1. Copier `.env.example` vers `.env`.
2. Configurer la base MySQL dans `.env`.
3. Faire pointer le virtual host Laragon vers `public/`.
4. Ouvrir `http://afyazone.test`.

## Base de donnees

Apres avoir configure `.env` et demarre MySQL, executer les migrations :

```text
php database/migrate.php
```

La commande cree la base locale configuree si elle n'existe pas, puis installe les tables de fondation et enregistre les migrations executees.

L'envoi des emails utilise Mailpit via SMTP. Laragon doit lancer Mailpit sur `127.0.0.1:1025` ; l'interface de lecture est disponible sur `http://127.0.0.1:8025`. Les identifiants SMTP restent vides par defaut. Les liens sensibles sont uniquement transmis par email et ne sont jamais affiches dans les pages.

## Securite transversale

Les composants de securite sont disponibles dans `app/Core` : session securisee, CSRF, validation, authentification, stockage prive des fichiers, journalisation et middlewares `Guest`, `Authenticate`, `Role`, `Permission` et `CsrfMiddleware`.

Les routes peuvent declarer leurs middlewares en troisieme argument :

```php
$router->post('/exemple', [ExampleController::class, 'store'], [CsrfMiddleware::class]);
```

## Routes du Sprint 1

```text
GET /              Accueil temporaire
GET /admin         Dashboard temporaire
GET /health-check  Verification PHP/MySQL
```

## Routes Auth

```text
GET  /connexion
POST /connexion
POST /deconnexion
GET  /verification-email
POST /verification-email/resend
GET  /verifier-email/{token}
GET  /inscription
POST /inscription
GET  /inscription/marchand
POST /inscription/marchand
GET  /mot-de-passe-oublie
POST /mot-de-passe-oublie
GET  /reset-password/{token}
POST /reset-password/{token}
GET  /compte
GET  /marchand
```

## Prochaine etape

La verification email doit etre configuree avant de passer aux layouts complets et au module Catalogue.
