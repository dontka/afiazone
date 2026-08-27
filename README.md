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

## Prochaine etape

Apres validation de la base de donnees, commencer la securite transversale puis le module Auth et RBAC.
