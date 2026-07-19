# AfiaZone

AfiaZone est une marketplace intelligente de produits de sante construite en PHP natif, MySQL et architecture MVC modulaire sans framework applicatif.

## Demarrage local

1. Copier `.env.example` vers `.env`.
2. Configurer la base MySQL dans `.env`.
3. Faire pointer le virtual host Laragon vers `public/`.
4. Ouvrir `http://afyazone.test`.

## Routes du Sprint 1

```text
GET /              Accueil temporaire
GET /admin         Dashboard temporaire
GET /health-check  Verification PHP/MySQL
```

## Prochaine etape

Apres validation du socle MVC, commencer le module Auth et RBAC.
