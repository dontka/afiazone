# PHASE E – Quick Test Guide

**Tester rapidement tous les endpoints et vues de la Phase E**

---

## 🚀 Démarrage Rapide

### 1. Démarrer le serveur
```bash
cd c:\laragon\www\afiazone
php -S localhost:8000
```

### 2. Setup initial (si nécessaire)
```bash
# Charger la base de données
php bin/setup-db.php

# Créer un utilisateur test
mysql -u root -proot afiazone < database/seeders/users.sql
```

---

## 👤 Test 1: User Profile Management

### Créer un compte test
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test123!",
    "first_name": "Jean",
    "last_name": "Doe"
  }'
```

Réponse attendue:
```json
{
  "success": true,
  "message": "Registration successful",
  "token": "eyJ0eXAiOiJKV1Q..."
}
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test123!"
  }'
```

Sauvegarder le token retourné : `TOKEN="eyJ0eXAiOiJKV1Q..."`

### Récupérer mon profil
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/me
```

Vérifier : 
- ✅ Status 200
- ✅ first_name, last_name, email présents
- ✅ avatar_url présent

### Mettre à jour le profil
```bash
curl -X PUT http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "city": "Kinshasa",
    "country": "Democratic Republic of Congo"
  }'
```

Vérifier :
- ✅ Status 200
- ✅ first_name mis à jour

### Upload Avatar
```bash
curl -X POST http://localhost:8000/api/me/avatar \
  -H "Authorization: Bearer $TOKEN" \
  -F "avatar=@/path/to/avatar.jpg"
```

Vérifier :
- ✅ Status 200
- ✅ avatar_url retournée

### Changer le mot de passe
```bash
curl -X POST http://localhost:8000/api/me/password \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "Test123!",
    "new_password": "NewPass123!",
    "confirm_password": "NewPass123!"
  }'
```

Vérifier : ✅ Status 200

---

## 🆔 Test 2: KYC Workflow

### Vérifier le statut KYC
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/kyc
```

Vérifier :
- ✅ Status 200
- ✅ status: "no_submission" ou "pending"
- ✅ documents: []

### Upload un document KYC
```bash
# Créer un fichier test
echo "test" > test_document.txt

# Upload
curl -X POST http://localhost:8000/api/kyc/documents \
  -H "Authorization: Bearer $TOKEN" \
  -F "document_type=id_card" \
  -F "document=@test_document.txt"
```

Vérifier :
- ✅ Status 200
- ✅ file_url retournée
- ✅ document_id retourné

### Upload un vrai document (image)
```bash
curl -X POST http://localhost:8000/api/kyc/documents \
  -H "Authorization: Bearer $TOKEN" \
  -F "document_type=proof_of_address" \
  -F "document=@/path/to/bill.jpg"
```

Vérifier : ✅ Status 200

### Valider la complétude
```bash
curl -X POST http://localhost:8000/api/kyc/validate \
  -H "Authorization: Bearer $TOKEN"
```

Réponse :
```json
{
  "success": true,
  "submission_id": 1,
  "status": "pending",
  "message": "All required documents submitted"
}
```

### Récupérer la soumission complète
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/kyc
```

Vérifier :
- ✅ status: "pending"
- ✅ documents: [array with uploaded docs]
- ✅ submission_date présente

---

## 👨‍💼 Test 3: Admin KYC Moderation

### Créer un compte admin (DB)
```sql
INSERT INTO users (email, password_hash, first_name, last_name, status) 
VALUES ('admin@example.com', '$2y$10$...hashed', 'Admin', 'User', 'active');

INSERT INTO user_roles (user_id, role_id) 
VALUES (2, 1);  -- 1 = admin
```

### Login admin
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "AdminPass123!"
  }'
```

Sauvegarder: `ADMIN_TOKEN="eyJ0eXAiOiJKV1Q..."`

### Lister les soumissions KYC
```bash
curl -H "Authorization: Bearer $ADMIN_TOKEN" \
  "http://localhost:8000/api/admin/kyc?status=pending&page=1"
```

Vérifier :
- ✅ Status 200
- ✅ data: array avec user_id = 1
- ✅ pagination données

### Voir les soumissions en attente
```bash
curl -H "Authorization: Bearer $ADMIN_TOKEN" \
  "http://localhost:8000/api/admin/kyc/pending"
```

### Voir détail d'une soumission
```bash
curl -H "Authorization: Bearer $ADMIN_TOKEN" \
  http://localhost:8000/api/admin/kyc/1
```

Vérifier :
- ✅ Status 200
- ✅ user data complet
- ✅ documents array

### Approver une soumission
```bash
curl -X POST http://localhost:8000/api/admin/kyc/1/approve \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Documents verified"}'
```

Vérifier :
- ✅ Status 200
- ✅ status: "approved"

### Vérifier l'approbation du côté user
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/kyc
```

Vérifier : ✅ status: "approved"

### Tester le rejet
```bash
# Upload nouveau doc (user 3 pour test)
curl -X POST http://localhost:8000/api/kyc/documents \
  -H "Authorization: Bearer $TOKEN_USER3" \
  -F "document_type=id_card" \
  -F "document=@test.jpg"

# Admin rejette
curl -X POST http://localhost:8000/api/admin/kyc/2/reject \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rejection_reason": "Document not clear"}'
```

Vérifier : ✅ status: "rejected"

---

## 🏪 Test 4: Merchant Registration

### Créer un marchand
```bash
curl -X POST http://localhost:8000/api/merchants \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "business_name": "PharmaPro",
    "business_type": "retailer",
    "description": "Pharmacy retail store",
    "contact_person": "Jean Doe",
    "contact_phone": "+243987654321",
    "warehouse_address": "123 Main Street",
    "warehouse_city": "Kinshasa",
    "warehouse_country": "Democratic Republic of Congo",
    "return_policy": "30 days return",
    "processing_time_days": 1,
    "accepts_cod": true,
    "accepts_wallet": true,
    "commission_percent": 15
  }'
```

Vérifier :
- ✅ Status 200 ou 201
- ✅ merchant_id retourné
- ✅ user associé au merchant

### Récupérer le profil marchand
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/merchants/1
```

Vérifier :
- ✅ Status 200
- ✅ business_name, business_type, etc.

### Mettre à jour informations livraison
```bash
curl -X PUT http://localhost:8000/api/merchants/1/shipping \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "warehouse_address": "456 New Street",
    "processing_time_days": 2
  }'
```

Vérifier : ✅ Status 200

---

## 🌐 Test 5: Frontend Views (Browser)

### KYC Submission Page
```
URL: http://localhost:8000/user/kyc-submission.php
```

Vérifier (logged in):
- ✅ Page charge
- ✅ Statut KYC affiché
- ✅ Upload form visible
- ✅ Drag-drop fonctionne
- ✅ Upload document réussit

### Client Profile Page
```
URL: http://localhost:8000/me
```

Vérifier (logged in):
- ✅ Onglets s'affichent
- ✅ Onglet KYC cliquable
- ✅ Statut KYC visible
- ✅ Upload documents fonctionne

### Merchant Registration Page
```
URL: http://localhost:8000/user/merchant-registration.php
```

Vérifier (logged in):
- ✅ Formulaire multi-step charge
- ✅ Validation step 1
- ✅ Navigation next/previous
- ✅ Review étape 4 fonctionne
- ✅ Soumission réussit

### Admin KYC Dashboard
```
URL: http://localhost:8000/admin/kyc-moderation.php
```

Vérifier (admin logué):
- ✅ Page charge
- ✅ Tableau KYC visible
- ✅ Filtres fonctionnent
- ✅ Search fonctionne
- ✅ Modal détail ouvre
- ✅ Actions (approver/rejeter) fonctionnent

---

## 📊 Checklist Test Complet

```
USER PROFILE
├─ [ ] POST /api/auth/register
├─ [ ] POST /api/auth/login
├─ [ ] GET /api/me
├─ [ ] PUT /api/me
├─ [ ] POST /api/me/password
├─ [ ] POST /api/me/avatar
├─ [ ] GET /api/users/{id}
└─ [ ] PUT /api/users/{id}

KYC
├─ [ ] GET /api/kyc
├─ [ ] POST /api/kyc
├─ [ ] POST /api/kyc/documents
├─ [ ] POST /api/kyc/validate
├─ [ ] GET /api/admin/kyc
├─ [ ] GET /api/admin/kyc/pending
├─ [ ] GET /api/admin/kyc/{id}
├─ [ ] POST /api/admin/kyc/{id}/approve
├─ [ ] POST /api/admin/kyc/{id}/reject
└─ [ ] POST /api/admin/kyc/{id}/request-revision

MERCHANT
├─ [ ] POST /api/merchants
├─ [ ] GET /api/merchants/{id}
├─ [ ] PUT /api/merchants/{id}
├─ [ ] GET /api/merchants/{id}/tier
├─ [ ] PUT /api/merchants/{id}/shipping
└─ [ ] PUT /api/merchants/{id}/fees

FRONTEND
├─ [ ] Client Profile Page loads
├─ [ ] KYC Submission Page loads
├─ [ ] Merchant Registration Page loads
├─ [ ] Admin KYC Dashboard loads
├─ [ ] Upload documents works
├─ [ ] Approve/Reject works
└─ [ ] Error messages display correctly
```

---

## 🐛 Troubleshooting

### 401 Unauthorized
**Cause**: Token manquant ou expiré  
**Solution**: 
```bash
# Re-login
TOKEN=$(curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test123!"}' \
  | jq -r '.token')
```

### 403 Forbidden
**Cause**: Pas de permission (non-admin)  
**Solution**: Utiliser le token admin : `$ADMIN_TOKEN`

### 422 Validation Error
**Cause**: Données invalides  
**Solution**: Vérifier le format des données vs API

### File Upload fails
**Cause**: Type ou taille fichier incorrect  
**Solution**: Utiliser JPG/PNG < 5MB

### Views not loading
**Cause**: Routes non configurées  
**Solution**: Ajouter les routes dans `routes/api.php`

---

## 📈 Performance Check

### Temps réponse cibles
```
GET /api/me              : < 100ms
POST /api/kyc/documents  : < 2s  (upload)
GET /api/admin/kyc       : < 500ms
```

### Memory usage
```
Per request: < 50MB
Admin dashboard: < 100MB
```

---

**✅ Tests Complets = Phase E Validée**

Prêt pour Phase F ! 🚀
