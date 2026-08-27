# Plan de developpement logique AfiaZone de A a Z

## 1. Objectif du document

Ce document donne l'ordre logique de construction de AfiaZone, depuis le cadrage produit jusqu'au lancement public.

Le principe est simple : on ne developpe une fonctionnalite que lorsque ses fondations existent deja. Par exemple, on ne developpe pas le paiement avant les commandes, on ne developpe pas les commandes avant le panier, on ne developpe pas le panier avant le catalogue et le stock, et on ne developpe pas l'IA avancee avant d'avoir des donnees reelles a exploiter.

AfiaZone sera developpe en PHP natif, MySQL, sans framework applicatif, avec une architecture MVC modulaire. Les modules doivent rester independants dans leur dossier, mais leur livraison suit une progression metier coherente.

## 2. Vision produit a construire

AfiaZone est une marketplace intelligente de produits de sante qui permet aux patients, pharmacies, laboratoires, cliniques, medecins, livreurs et partenaires de localiser, commander, vendre et recevoir rapidement des medicaments, dispositifs medicaux, produits de diagnostic, produits de soins, equipements de protection et produits de nutrition medicale.

La plateforme doit permettre :

1. A un patient de trouver un produit de sante disponible pres de lui.
2. A une pharmacie ou structure de soins de vendre ses produits en ligne.
3. A AfiaZone de securiser les achats sensibles, surtout les produits sous ordonnance.
4. A un livreur ou a un point de retrait de valider la remise avec un QR code ou un token.
5. A l'administration de verifier les vendeurs, les produits, les ordonnances et les transactions.
6. A l'IA d'ameliorer la recherche, la disponibilite, la detection d'anomalies et l'assistance, sans remplacer un professionnel de sante.
7. Au wallet sante d'arriver ensuite, une fois la marketplace stable et conforme.

## 3. Regle de progression du projet

Le developpement doit suivre cette chaine :

```text
Cadrage
  -> Environnement technique
  -> Noyau MVC
  -> Base de donnees
  -> Securite transversale
  -> Authentification et roles
  -> Layouts et templates
  -> Catalogue medical
  -> Vendeurs et KYC
  -> Stocks par vendeur
  -> Recherche et geolocalisation
  -> Panier
  -> Commandes
  -> Ordonnances
  -> Paiements
  -> Livraison/retrait
  -> Avis et reputation
  -> Administration, moderation et conformite
  -> Notifications
  -> IA utile
  -> Wallet sante
  -> Tests, staging, lancement
```

Chaque etape doit produire :

1. Des tables MySQL si la fonctionnalite stocke des donnees.
2. Des models/repositories pour acceder aux donnees.
3. Des services pour la logique metier.
4. Des controllers pour les requetes web/API.
5. Des vues frontend/backend.
6. Des routes.
7. Un critere de validation manuel ou automatise.

## 4. Architecture cible avant de coder les modules

### 4.1 Structure generale

```text
afyazone/
  app/
    Core/
      App.php
      Router.php
      Controller.php
      Model.php
      View.php
      Database.php
      Request.php
      Response.php
      Session.php
      Auth.php
      Csrf.php
      Validator.php
      FileStorage.php
      Mailer.php
      Logger.php
    Modules/
      Auth/
      Users/
      Catalog/
      Sellers/
      Kyc/
      Inventory/
      Search/
      Cart/
      Orders/
      Prescriptions/
      Payments/
      Delivery/
      Reviews/
      Admin/
      Moderation/
      Compliance/
      Notifications/
      Ai/
      Wallet/
      Reports/
    Shared/
      Helpers/
      DTO/
      Exceptions/
      Policies/
      ValueObjects/
  config/
    app.php
    database.php
    mail.php
    payments.php
    ai.php
    storage.php
  database/
    migrations/
    seeders/
    schema.sql
  public/
    index.php
    .htaccess
    assets/
  storage/
    logs/
    cache/
    sessions/
    uploads/
      prescriptions/
      kyc/
      products/
    exports/
  tests/
    unit/
    integration/
  .env.example
  composer.json
  README.md
```

### 4.2 Regle d'independance des modules

Un module ne doit pas acceder directement aux tables d'un autre module depuis son controller. Si un module a besoin d'une action externe, il passe par un service public du module concerne.

Exemples :

1. `Orders` ne modifie pas directement `seller_products`; il appelle `InventoryService::reserveStock()`.
2. `Payments` ne change pas directement le statut final d'une commande; il appelle `OrderPaymentService::markAsPaid()`.
3. `Delivery` ne termine pas une commande seul; il appelle `OrderFulfillmentService::markAsDelivered()`.
4. `Ai` ne valide pas une ordonnance; il fournit un resultat d'aide a `Prescriptions`.

## 5. Etape 0 : cadrage fonctionnel et reglementaire

### Objectif

Clarifier exactement ce que le MVP doit faire et ce qui sera reporte.

### A faire

1. Valider les categories de produits de sante.
2. Valider les types d'utilisateurs : administrateur, moderateur, marchand, client, livreur, partenaire.
3. Definir les produits vendables sans ordonnance.
4. Definir les produits vendables uniquement avec ordonnance.
5. Definir les regles de verification des marchands.
6. Definir les documents KYC obligatoires.
7. Definir les modes de paiement du MVP : paiement a la livraison d'abord, mobile money ensuite.
8. Definir les zones pilotes de lancement.
9. Definir les limites de responsabilite de l'IA : assistance, recherche, OCR, detection, mais pas diagnostic medical.

### Livrables

1. Liste des fonctionnalites MVP.
2. Liste des fonctionnalites post-MVP.
3. Liste des roles et permissions.
4. Liste des statuts commande.
5. Liste des statuts ordonnance.
6. Liste des statuts marchand/KYC.

### Validation

Le projet peut demarrer si le parcours suivant est clair : un client cherche un produit, choisit un vendeur, commande, fournit une ordonnance si necessaire, paie, recoit ou retire le produit, puis evalue le vendeur.

## 6. Etape 1 : environnement technique local

### Depend de

Etape 0 terminee.

### Objectif

Preparer une base de travail stable sur Laragon.

### A faire

1. Utiliser PHP 8.2 ou plus.
2. Utiliser MySQL 8 ou MariaDB compatible.
3. Configurer un virtual host local : `afyazone.test`.
4. Faire pointer le virtual host vers `public/`.
5. Creer `.env.example`.
6. Creer `.env` local non versionne.
7. Creer `composer.json` meme sans framework, pour l'autoload et quelques bibliotheques ciblees.
8. Creer les dossiers `app`, `config`, `database`, `public`, `storage`, `tests`.

### Fichiers a creer

```text
public/index.php
public/.htaccess
config/app.php
config/database.php
.env.example
composer.json
README.md
```

### Validation

En ouvrant `http://afyazone.test`, PHP affiche une page temporaire AfiaZone sans erreur.

## 7. Etape 2 : noyau MVC minimal

### Depend de

Etape 1 terminee.

### Objectif

Obtenir un squelette applicatif capable de recevoir une URL, executer un controller et afficher une vue.

### A faire

1. Creer `App\Core\Router`.
2. Creer `App\Core\Controller`.
3. Creer `App\Core\View`.
4. Creer `App\Core\Request`.
5. Creer `App\Core\Response`.
6. Creer `App\Core\Session`.
7. Creer `App\Core\Database` avec PDO.
8. Charger les fichiers `routes.php` de chaque module actif.
9. Ajouter la gestion des erreurs 404 et 500.
10. Ajouter un layout public minimal.
11. Ajouter un layout backend minimal.

### Routes initiales

```text
GET /              HomeController@index
GET /admin         DashboardController@index
GET /health-check  HealthCheckController@index
```

### Validation

1. `/` affiche une page publique.
2. `/admin` affiche un dashboard temporaire.
3. `/health-check` confirme que PHP et MySQL sont disponibles.
4. Une route inconnue retourne une vraie page 404.

## 8. Etape 3 : base de donnees de fondation

### Depend de

Etape 2 terminee.

### Objectif

Installer les tables communes que les modules utiliseront ensuite.

### A faire

1. Creer le dossier `database/migrations`.
2. Creer un systeme simple d'execution de migrations.
3. Creer la table `migrations`.
4. Creer les tables utilisateurs, roles, permissions et logs d'audit.
5. Ajouter les timestamps `created_at`, `updated_at` partout ou necessaire.
6. Utiliser des cles primaires `BIGINT UNSIGNED AUTO_INCREMENT`.
7. Prevoir des UUID publics pour les entites exposees.

### Tables a creer en premier

```sql
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL UNIQUE,
  email VARCHAR(190) NULL UNIQUE,
  phone VARCHAR(30) NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('pending','active','suspended','deleted') NOT NULL DEFAULT 'pending',
  email_verified_at DATETIME NULL,
  phone_verified_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL
);

CREATE TABLE permissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  label VARCHAR(160) NOT NULL
);

CREATE TABLE role_permissions (
  role_id INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id)
);

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id BIGINT UNSIGNED NULL,
  action VARCHAR(160) NOT NULL,
  entity_type VARCHAR(120) NULL,
  entity_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  metadata JSON NULL,
  created_at DATETIME NOT NULL
);
```

### Validation

Une commande locale ou une page admin technique peut executer les migrations et confirmer que les tables existent.

## 9. Etape 4 : securite transversale avant les comptes

### Depend de

Etape 3 terminee.

### Objectif

Ajouter les protections qui seront utilisees par tous les modules.

### A faire

1. Creer `Csrf` pour les formulaires.
2. Creer `Validator` pour valider les entrees.
3. Creer `Auth` pour lire l'utilisateur connecte.
4. Creer middleware `guest`, `auth`, `role`, `permission`.
5. Creer `FileStorage` pour eviter les uploads directs dans `public/`.
6. Creer `Logger` pour les erreurs et actions critiques.
7. Configurer les cookies de session : `HttpOnly`, `SameSite`, `Secure` en production.
8. Ajouter l'echappement HTML par defaut dans les vues.

### Validation

1. Un formulaire sans token CSRF est rejete.
2. Une page protegee redirige un visiteur non connecte vers la connexion.
3. Une erreur est ecrite dans `storage/logs` sans afficher de details SQL au navigateur.

## 10. Etape 5 : module Auth et comptes utilisateurs

### Depend de

Etapes 3 et 4 terminees.

### Objectif

Permettre aux utilisateurs de creer un compte, se connecter et obtenir un role avec un design UI et UX impécable.

### A faire

1. Creer l'inscription client.
2. Creer l'inscription marchand basique.
3. Creer la connexion.
4. Creer la deconnexion.
5. Creer le reset password.
6. Hasher les mots de passe avec `password_hash()`.
7. Verifier les mots de passe avec `password_verify()`.
8. Regenerer l'ID de session apres connexion.
9. Ajouter une limitation des tentatives de connexion.
10. Seeder les roles initiaux.

### Roles initiaux

```text
super_admin
admin
moderator
merchant
customer
courier
partner
```

### Tables supplementaires

```text
user_profiles
password_resets
login_attempts
user_sessions
account_levels
```

### Routes

```text
GET  /connexion
POST /connexion
POST /deconnexion
GET  /inscription
POST /inscription
GET  /inscription/marchand
POST /inscription/marchand
GET  /mot-de-passe-oublie
POST /mot-de-passe-oublie
GET  /reset-password/{token}
POST /reset-password/{token}
```

### Validation

1. Un client peut creer un compte et acceder a son tableau de bord.
2. Un marchand peut creer un compte et acceder a un dashboard marchand incomplet.
3. Un visiteur non connecte ne peut pas ouvrir les dashboards.
4. Un client ne peut pas ouvrir une page admin.

## 11. Etape 6 : layouts et adaptation des templates existants

### Depend de

Etape 5 terminee.

### Objectif

Transformer les templates HTML existants en vues reutilisables MVC.

### A faire

1. Identifier les assets CSS/JS/images utiles.
2. Copier ou servir les assets depuis `public/assets`.
3. Creer un layout public : header, navigation, footer, zone contenu.
4. Creer un layout client.
5. Creer un layout marchand.
6. Creer un layout admin.
7. Transformer les pages statiques en vues PHP partielles.
8. Ajouter flash messages et affichage erreurs de validation.

### Pages temporaires a obtenir

```text
/
/catalogue
/compte
/marchand
/admin
```

### Validation

Les pages principales utilisent les memes assets, sans duplication complete de HTML dans chaque vue.

## 12. Etape 7 : module Catalog, categories et produits canoniques

### Depend de

Etapes 3, 5 et 6 terminees.

### Objectif

Creer le catalogue medical central. A ce stade, un produit existe dans AfiaZone, mais il n'est pas encore vendu par un marchand, bref tout le flux produit coté front et back.

### A faire

1. Creer les categories racines.
2. Creer les sous-categories de medicaments.
3. Creer les marques/fabricants.
4. Creer les principes actifs.
5. Creer les fiches produits.
6. Ajouter le champ `requires_prescription`.
7. Ajouter le statut de moderation produit.
8. Ajouter images et documents produits.
9. Creer CRUD admin des categories.
10. Creer CRUD admin des produits.
11. Creer page publique catalogue.
12. Creer page publique detail produit.

### Categories racines

1. Medicaments pharmaceutiques.
2. Produits biologiques.
3. Dispositifs medicaux.
4. Produits de diagnostic.
5. Produits de soins et pansements.
6. Antiseptiques et desinfectants.
7. Nutrition medicale.
8. Medecine traditionnelle.

### Tables

```text
categories
brands
active_ingredients
products
product_active_ingredients
product_variants
product_images
product_documents
```

### Routes

```text
GET  /catalogue
GET  /categorie/{slug}
GET  /produit/{slug}
GET  /admin/categories
POST /admin/categories
GET  /admin/produits
POST /admin/produits
GET  /admin/produits/{id}/modifier
POST /admin/produits/{id}/modifier
```

### Validation

1. Un admin peut creer une categorie.
2. Un admin peut creer un produit.
3. Un produit publie apparait dans le catalogue.
4. Un produit sous ordonnance affiche clairement qu'une ordonnance sera exigee au checkout.

## 13. Etape 8 : module Sellers et KYC marchand

### Depend de

Etapes 5, 6 et 7 terminees.

### Objectif

Permettre a une pharmacie, clinique, laboratoire, grossiste, producteur ou detaillant de devenir vendeur verifie.

### A faire

1. Creer le profil vendeur.
2. Ajouter type d'etablissement.
3. Ajouter adresse, ville, commune, latitude, longitude.
4. Ajouter horaires d'ouverture.
5. Ajouter zones de livraison.
6. Ajouter options de retrait sur place.
7. Ajouter conditions de paiement a la livraison.
8. Ajouter depot de documents KYC.
9. Ajouter validation KYC par moderateur/admin.
10. Ajouter statut vendeur : brouillon, soumis, en_revision, verifie, rejete, suspendu.

### Tables

```text
sellers
seller_locations
seller_hours
seller_delivery_zones
seller_payment_options
kyc_profiles
kyc_documents
kyc_reviews
business_verifications
```

### Routes

```text
GET  /marchand/profil
POST /marchand/profil
GET  /marchand/kyc
POST /marchand/kyc/documents
GET  /admin/kyc
GET  /admin/kyc/{id}
POST /admin/kyc/{id}/decision
GET  /vendeur/{slug}
```

### Validation

1. Un marchand peut completer son profil.
2. Un marchand peut envoyer ses documents.
3. Un admin peut approuver ou rejeter le KYC.
4. Seul un marchand verifie pourra vendre effectivement.

## 14. Etape 9 : module Inventory, stock et prix par vendeur

### Depend de

Etapes 7 et 8 terminees.

### Objectif

Relier les produits canoniques du catalogue aux vendeurs qui les ont en stock.

### A faire

1. Permettre a un marchand verifie de selectionner un produit du catalogue.
2. Definir son prix.
3. Definir sa quantite disponible.
4. Definir le seuil d'alerte.
5. Ajouter lot et date d'expiration.
6. Masquer automatiquement les lots expires.
7. Creer historique des mouvements de stock.
8. Prevoir reservation temporaire pendant checkout.
9. Afficher au public les vendeurs qui ont le produit disponible.

### Tables

```text
seller_products
inventory_batches
stock_movements
stock_reservations
```

### Routes

```text
GET  /marchand/stock
POST /marchand/stock/ajouter-produit
GET  /marchand/stock/{id}/modifier
POST /marchand/stock/{id}/modifier
POST /marchand/stock/{id}/mouvement
GET  /api/products/{id}/availability
```

### Validation

1. Un marchand verifie peut rendre un produit disponible.
2. Un produit sans stock n'est pas achetable.
3. Un lot expire n'est pas propose a la vente.
4. La page produit affiche les vendeurs disponibles.

## 15. Etape 10 : recherche, filtres et geolocalisation

### Depend de

Etapes 7, 8 et 9 terminees.

### Objectif

Permettre au client de trouver rapidement un produit disponible selon nom, categorie, disponibilite et proximite.

### A faire

1. Recherche par nom de produit.
2. Recherche par categorie.
3. Recherche par principe actif.
4. Filtre par disponibilite.
5. Filtre par prix.
6. Filtre par distance.
7. Filtre par livraison ou retrait.
8. Calcul distance entre client et vendeur.
9. Journaliser les recherches pour la future IA.
10. Ajouter pagination.

### Tables

```text
search_logs
popular_queries
user_locations
```

### Index MySQL

```text
FULLTEXT products(name, short_description, description)
INDEX seller_products(product_id, status, quantity)
INDEX seller_locations(latitude, longitude)
```

### Routes

```text
GET /recherche?q=&lat=&lng=
GET /api/search/products?q=&lat=&lng=
```

### Validation

1. Une recherche retourne seulement des produits publies.
2. Les resultats indiquent les vendeurs disponibles.
3. Les produits plus proches peuvent etre classes avant les plus lointains.
4. Une recherche sans resultat est journalisee.

## 16. Etape 11 : module Cart, panier multi-vendeur

### Depend de

Etapes 9 et 10 terminees.

### Objectif

Permettre au client de preparer un achat avant la commande.

### A faire

1. Ajouter un produit vendeur au panier.
2. Modifier la quantite.
3. Supprimer une ligne.
4. Calculer sous-total par vendeur.
5. Calculer total general.
6. Detecter produits sous ordonnance.
7. Verifier le stock avant affichage checkout.
8. Gerer panier invite.
9. Convertir panier invite en panier client apres connexion.

### Tables

```text
carts
cart_items
```

### Routes

```text
GET    /panier
POST   /panier/ajouter
POST   /panier/{itemId}/modifier
POST   /panier/{itemId}/supprimer
POST   /api/cart/items
PATCH  /api/cart/items/{id}
DELETE /api/cart/items/{id}
```

### Validation

1. Le panier refuse une quantite superieure au stock disponible.
2. Le panier affiche clairement les produits sous ordonnance.
3. Un panier avec plusieurs vendeurs est possible.
4. Le checkout n'est accessible que si le panier est valide.

## 17. Etape 12 : module Orders, commande et machine d'etats

### Depend de

Etape 11 terminee.

### Objectif

Transformer un panier valide en commande tracable.

### A faire

1. Creer une commande depuis le panier.
2. Decouper la commande par vendeur.
3. Copier nom, prix et quantite dans `order_items` pour garder l'historique.
4. Reserver le stock.
5. Calculer sous-total, frais de service, livraison et total.
6. Generer numero de commande.
7. Gerer les statuts de commande.
8. Gerer historique de statuts.
9. Bloquer les transitions invalides.
10. Generer facture ou ticket provisoire.

### Etats commande

```text
draft
pending_prescription
prescription_rejected
pending_payment
paid
accepted_by_seller
preparing
ready_for_pickup
assigned_to_courier
out_for_delivery
delivered
completed
cancelled
refunded
```

### Tables

```text
orders
order_seller_groups
order_items
order_status_history
invoices
```

### Routes

```text
GET  /commande/checkout
POST /commande/creer
GET  /commande/{orderNumber}
GET  /compte/commandes
GET  /marchand/commandes
POST /marchand/commandes/{id}/accepter
POST /marchand/commandes/{id}/preparer
```

### Validation

1. Une commande conserve les prix au moment de l'achat.
2. Le stock est reserve apres creation de commande.
3. Une commande contenant un produit sous ordonnance passe en `pending_prescription`.
4. Une commande sans ordonnance passe en `pending_payment` ou `accepted_by_seller` selon le mode de paiement.

## 18. Etape 13 : module Prescriptions, ordonnances et validation humaine

### Depend de

Etapes 7, 11 et 12 terminees.

### Objectif

Securiser les commandes contenant des produits sous ordonnance.

### A faire

1. Exiger une ordonnance si au moins un produit de la commande l'impose.
2. Accepter image ou PDF.
3. Verifier taille, extension et MIME.
4. Stocker le fichier dans `storage/uploads/prescriptions`, pas en acces public direct.
5. Creer file de verification admin/moderateur.
6. Autoriser certains marchands verifies a participer a la verification si defini.
7. Ajouter decision : approuvee, rejetee, demande_information.
8. Ajouter motif de rejet.
9. Debloquer la commande si ordonnance approuvee.
10. Annuler ou rembourser si ordonnance rejetee apres paiement.

### Tables

```text
prescriptions
prescription_reviews
prescription_ai_checks
```

### Routes

```text
GET  /commande/{orderNumber}/ordonnance
POST /commande/{orderNumber}/ordonnance
GET  /admin/ordonnances
GET  /admin/ordonnances/{id}
POST /admin/ordonnances/{id}/decision
```

### Validation

1. Une commande sous ordonnance ne peut pas avancer sans document.
2. L'admin peut approuver ou rejeter l'ordonnance.
3. Toutes les decisions sont historisees.
4. L'IA, si presente plus tard, ne remplace jamais cette validation.

## 19. Etape 14 : module Payments, paiement et commissions

### Depend de

Etapes 12 et 13 terminees.

### Objectif

Gerer les paiements de facon tracable, en commencant par le paiement a la livraison puis mobile money.

### A faire MVP

1. Paiement a la livraison si le marchand l'autorise.
2. Statut paiement : unpaid, pending, paid, failed, refunded.
3. Calcul commission marketplace.
4. Calcul frais transaction/service.
5. Journal ledger pour chaque mouvement financier.

### A faire apres MVP

1. Integration mobile money sandbox.
2. Creation payment intent.
3. Webhooks provider.
4. Verification signature webhook.
5. Reconciliation.
6. Remboursement automatique ou manuel.

### Tables

```text
payment_methods
payments
payment_transactions
refunds
commissions
ledger_entries
```

### Routes

```text
POST /commande/{orderNumber}/paiement/cash-on-delivery
POST /payments/initiate
POST /payments/webhook/{provider}
GET  /admin/paiements
POST /admin/remboursements/{id}/valider
```

### Validation

1. Une commande sous ordonnance non approuvee ne peut pas etre payee definitivement.
2. Un paiement confirme cree une entree ledger.
3. Un remboursement cree une entree ledger inverse.
4. Les webhooks ne peuvent pas modifier une commande sans verification.

## 20. Etape 15 : module Delivery, retrait et validation de remise

### Depend de

Etapes 12 et 14 terminees.

### Objectif

Permettre au client de recevoir le produit ou de le retirer, avec preuve de remise.

### A faire

1. Choix livraison ou retrait pendant checkout.
2. Adresse client.
3. Frais livraison par zone ou distance.
4. Statuts livraison.
5. Attribution livreur.
6. Generation QR code.
7. Generation token a 5 chiffres.
8. Hash du token en base.
9. Validation de remise par QR ou token.
10. Preuve de livraison ou retrait.
11. Passage commande a `delivered`, puis `completed`.

### Tables

```text
deliveries
delivery_events
couriers
pickup_events
delivery_proofs
order_tokens
```

### Routes

```text
GET  /commande/{orderNumber}/suivi
POST /livraison/{id}/assigner
POST /livraison/{id}/statut
POST /livraison/valider-token
POST /api/delivery/validate-token
```

### Validation

1. Le token en clair n'est jamais stocke en base.
2. Une livraison ne se termine pas sans token ou QR valide.
3. Une commande livree peut declencher une demande d'evaluation.

## 21. Etape 16 : avis, reputation et confiance

### Depend de

Etape 15 terminee.

### Objectif

Construire la confiance par les evaluations verifiees.

### A faire

1. Autoriser un avis seulement apres commande completee.
2. Evaluer produit.
3. Evaluer vendeur.
4. Evaluer livraison.
5. Moderer les commentaires.
6. Recalculer score vendeur.
7. Recalculer score produit.
8. Permettre signalement d'avis abusif.

### Tables

```text
reviews
review_reports
seller_scores
product_scores
```

### Routes

```text
POST /commande/{orderNumber}/avis
GET  /produit/{slug}/avis
GET  /vendeur/{slug}/avis
GET  /admin/avis
POST /admin/avis/{id}/moderation
```

### Validation

1. Un client ne peut pas evaluer un produit non achete.
2. Un avis signale arrive en moderation.
3. Les scores publics changent apres avis valide.

## 22. Etape 17 : administration, moderation et conformite

### Depend de

Etapes 7 a 16 terminees.

### Objectif

Donner a AfiaZone les outils de controle necessaires pour exploiter la marketplace.

### A faire admin

1. Dashboard global.
2. Gestion utilisateurs.
3. Gestion roles et permissions.
4. Gestion marchands.
5. Gestion KYC.
6. Gestion categories.
7. Gestion produits.
8. Gestion stocks sensibles.
9. Gestion commandes.
10. Gestion paiements/remboursements.
11. Gestion ordonnances.
12. Gestion avis et signalements.
13. Gestion commissions.
14. Rapports.
15. Logs audit.

### A faire conformite

1. Marquer les produits essentiels selon les references disponibles.
2. Marquer les produits soumis a restriction.
3. Enregistrer statut AMM/enregistrement si connu.
4. Verifier vendeur certifie si les donnees sont disponibles.
5. Conserver les decisions critiques dans `audit_logs`.
6. Definir politique de conservation des ordonnances.

### Tables

```text
moderation_cases
moderation_actions
flags
compliance_references
product_compliance
seller_compliance
audit_logs
reports
```

### Validation

1. Un admin peut suivre toute la chaine commande.
2. Un moderateur peut traiter KYC, produits, ordonnances et avis selon ses permissions.
3. Toute action critique laisse une trace dans l'audit.

## 23. Etape 18 : notifications

### Depend de

Etapes 12 a 17 terminees.

### Objectif

Informer chaque acteur au bon moment sans disperser la logique dans tous les modules.

### A faire

1. Creer service central `NotificationService`.
2. Creer templates de notification.
3. Creer notifications internes.
4. Ajouter email si SMTP disponible.
5. Ajouter SMS/WhatsApp si provider disponible.
6. Ajouter file d'attente des messages.
7. Ajouter logs d'envoi.

### Evenements

```text
account_created
kyc_submitted
kyc_approved
order_created
prescription_required
prescription_approved
prescription_rejected
payment_confirmed
order_ready
courier_assigned
order_delivered
review_requested
```

### Tables

```text
notifications
notification_templates
notification_logs
notification_queue
```

### Validation

Une commande completee envoie une notification client et cree une demande d'evaluation.

## 24. Etape 19 : IA utile et progressive

### Depend de

Etapes 10, 12, 13, 16 et 17 terminees.

### Objectif

Ajouter l'intelligence artificielle seulement quand les donnees metier existent deja.

### Pourquoi l'IA arrive ici

Avant cette etape, la plateforme n'a pas assez de donnees fiables. L'IA doit apprendre des recherches, stocks, commandes, avis, prix et decisions de moderation. La mettre trop tot produit des suggestions faibles et difficiles a controler.

### Cas d'usage dans l'ordre

1. Recherche intelligente : corriger fautes, synonymes, noms commerciaux, principes actifs.
2. Classement intelligent : disponibilite, distance, prix, score vendeur, delai estime.
3. Suggestions proches : alternatives informatives, jamais diagnostic.
4. OCR ordonnance : extraire du texte pour aider le moderateur.
5. Prediction rupture : alerter marchand/admin.
6. Detection anomalie : prix anormal, comportement vendeur suspect, commande inhabituelle.
7. Assistant support : expliquer les commandes, paiements, livraisons, KYC, ordonnances.

### Architecture module IA

```text
Ai/
  Controllers/
    AiSearchController.php
    AiAssistantController.php
  Services/
    AiClient.php
    SearchIntentService.php
    RecommendationService.php
    StockForecastService.php
    FraudSignalService.php
    PrescriptionOcrService.php
  Repositories/
    AiLogRepository.php
    SearchSignalRepository.php
  routes.php
```

### Tables

```text
ai_events
ai_search_queries
ai_recommendations
ai_risk_signals
ai_ocr_results
```

### Garde-fous

1. L'IA ne produit pas de diagnostic medical.
2. L'IA ne remplace pas un medecin, pharmacien, moderateur ou admin.
3. L'IA ne valide jamais une ordonnance seule.
4. Le systeme doit fonctionner en mode degrade sans IA.
5. Les donnees sensibles doivent etre masquees dans les logs IA autant que possible.

### Validation

1. La recherche fonctionne meme si le service IA est indisponible.
2. Une suggestion IA affiche une indication informative.
3. Une ordonnance OCRisee reste en attente de validation humaine.

## 25. Etape 20 : wallet sante et services financiers avances

### Depend de

Marketplace stable, paiements stabilises, validation reglementaire obtenue.

### Objectif

Ajouter le wallet apres la marketplace, pas avant, car il augmente fortement la complexite financiere et reglementaire.

### A faire

1. Compte wallet par utilisateur eligible.
2. Ledger wallet separe du ledger marketplace.
3. Depot mobile money.
4. Paiement commande par wallet.
5. Contribution familiale affectee a la sante.
6. Paiement assurance/mutuelle.
7. Gel de transaction suspecte.
8. Reconciliation.
9. Rapports financiers.
10. Audit renforce.

### Tables

```text
wallets
wallet_transactions
wallet_holds
family_contributions
insurance_products
insurance_payments
wallet_ledger_entries
```

### Validation

1. Une transaction wallet ne peut pas exister sans ligne ledger.
2. Le solde wallet est recalculable depuis le ledger.
3. Une transaction suspecte peut etre gelee.

## 26. Etape 21 : tests, donnees de demonstration et qualite

### Depend de

Chaque module doit avoir au moins ses validations manuelles avant d'etre considere termine.

### Tests manuels par parcours

1. Visiteur consulte catalogue.
2. Client cree un compte.
3. Marchand cree un compte.
4. Marchand soumet KYC.
5. Admin valide KYC.
6. Admin cree produit canonique.
7. Marchand ajoute stock et prix.
8. Client recherche un produit disponible.
9. Client ajoute au panier.
10. Client commande sans ordonnance.
11. Client commande avec ordonnance.
12. Admin valide ordonnance.
13. Paiement a la livraison est choisi.
14. Marchand prepare commande.
15. Livreur valide token.
16. Client depose avis.
17. Admin consulte rapport.

### Tests automatises prioritaires

1. Router.
2. Validator.
3. Auth/session.
4. Permissions.
5. Calcul panier.
6. Reservation stock.
7. Machine d'etats commande.
8. Upload ordonnance.
9. Generation token.
10. Verification token.
11. Calcul commissions.
12. Mode degrade IA.

### Donnees de demonstration

1. 1 super admin.
2. 1 admin.
3. 1 moderateur.
4. 3 clients.
5. 3 pharmacies.
6. 1 clinique.
7. 1 laboratoire.
8. 1 livreur.
9. 8 categories racines.
10. 50 produits.
11. 20 produits disponibles en stock.
12. 5 commandes sans ordonnance.
13. 5 commandes avec ordonnance.

## 27. Etape 22 : preproduction et lancement

### Depend de

MVP valide en local et donnees de demonstration pretes.

### Preproduction

1. Installer l'application sur un serveur staging.
2. Configurer HTTPS.
3. Configurer base staging.
4. Configurer SMTP/SMS sandbox si disponible.
5. Tester mobile money sandbox si disponible.
6. Tester uploads et permissions fichiers.
7. Tester backups.
8. Tester logs erreurs.
9. Tester comptes admin avec acces restreint.
10. Faire une recette complete avec utilisateurs pilotes.

### Production

1. Serveur Linux, Apache/Nginx, PHP-FPM, MySQL.
2. Document root vers `public/`.
3. `.env` production hors depot Git.
4. HTTPS obligatoire.
5. Backups MySQL automatiques.
6. Rotation logs.
7. Monitoring espace disque uploads.
8. Monitoring erreurs PHP.
9. Compte super admin protege.
10. Plan de restauration.

### Validation lancement

AfiaZone peut etre lance si un parcours complet fonctionne en production : recherche produit, commande, ordonnance si necessaire, paiement choisi, preparation, livraison/retrait, validation token, avis.

## 28. Definition claire du MVP

### MVP a livrer

1. Socle MVC PHP/MySQL sans framework.
2. Authentification et roles.
3. Catalogue medical.
4. KYC marchand.
5. Stock par marchand.
6. Recherche simple avec filtres.
7. Panier.
8. Commande.
9. Ordonnance avec validation humaine.
10. Paiement a la livraison.
11. Livraison ou retrait.
12. QR code ou token de remise.
13. Avis client.
14. Dashboard marchand.
15. Dashboard admin/moderation.
16. Notifications internes minimum.

### Hors MVP

1. Wallet complet.
2. Assurance/mutuelle complete.
3. IA avancee de prediction.
4. Multiples providers mobile money.
5. Application mobile native.
6. Automatisation complete de verification ordonnance.

## 29. Ordre exact de creation du code

1. Creer la structure de dossiers.
2. Configurer `public/index.php` et `.htaccess`.
3. Creer le noyau MVC.
4. Connecter MySQL avec PDO.
5. Creer migrations et seeders.
6. Creer securite transversale : CSRF, session, validator, auth middleware.
7. Creer Auth et RBAC.
8. Adapter layouts frontend/backend.
9. Creer Catalog.
10. Creer Sellers et KYC.
11. Creer Inventory.
12. Creer Search.
13. Creer Cart.
14. Creer Orders.
15. Creer Prescriptions.
16. Creer Payments basique.
17. Creer Delivery et token.
18. Creer Reviews.
19. Creer Admin/Moderation/Compliance.
20. Creer Notifications.
21. Ajouter IA utile.
22. Ajouter Wallet.
23. Tester MVP.
24. Deployer staging.
25. Deployer production.

## 30. Prochaine action concrete

La prochaine action n'est pas encore de coder tous les modules. La bonne premiere action est de creer le socle MVC minimal.

### Sprint 1 : socle MVC

Objectif : afficher `/`, `/admin` et `/health-check` via le routeur MVC.

A creer :

1. `public/index.php`.
2. `public/.htaccess`.
3. `config/app.php`.
4. `config/database.php`.
5. `app/Core/Router.php`.
6. `app/Core/Controller.php`.
7. `app/Core/View.php`.
8. `app/Core/Database.php`.
9. `app/Modules/Home/Controllers/HomeController.php`.
10. `app/Modules/Admin/Controllers/DashboardController.php`.
11. `app/Modules/System/Controllers/HealthCheckController.php`.
12. `app/Modules/Home/routes.php`.
13. `app/Modules/Admin/routes.php`.
14. `app/Modules/System/routes.php`.

Critere de fin du sprint 1 :

1. `http://afyazone.test/` affiche l'accueil.
2. `http://afyazone.test/admin` affiche un dashboard temporaire.
3. `http://afyazone.test/health-check` confirme la connexion MySQL.
4. Une URL inconnue affiche une page 404 propre.

Apres ce sprint seulement, il devient logique de commencer Auth et RBAC.