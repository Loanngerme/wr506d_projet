# Application Symfony + Vue.js - Films et Acteurs

Application web full-stack avec authentification JWT, 2FA, gestion de films, acteurs et commentaires.

## Table des matières

- [Technologies](#technologies)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Lancement](#lancement)
- [API Endpoints](#api-endpoints)
- [Structure du projet](#structure-du-projet)
- [Commandes utiles](#commandes-utiles)

## Technologies

### Backend
- **Symfony 7.2** - Framework PHP
- **API Platform 4.1** - REST API et GraphQL
- **LexikJWTAuthenticationBundle** - Authentification JWT
- **OTPHP + QR Code** - Authentification à deux facteurs (2FA)
- **Doctrine ORM** - Gestion de base de données
- **MariaDB 10.8** - Base de données
- **Docker** - Conteneurisation

### Frontend
- **Vue.js 3** - Framework JavaScript
- **TypeScript** - Typage statique
- **Vite** - Build tool
- **Vue Router 4** - Routage
- **Pinia** - Gestion d'état
- **Axios** - Client HTTP

## Prérequis

- Docker et Docker Compose
- Node.js >= 20.19.0 ou >= 22.12.0
- npm ou yarn
- PHP >= 8.2 (pour développement local sans Docker)
- Composer (pour développement local sans Docker)

## Installation

### 1. Cloner le projet

```bash
git clone <repository-url>
cd wr506d_projet
```

### 2. Installation Backend (Symfony)

#### Avec Docker (Recommandé)

```bash
# Démarrer les conteneurs
docker-compose up -d

# Attendre le démarrage de la base de données
sleep 10

# Installer les dépendances PHP
docker exec symfony-web composer install

# Générer les clés JWT
docker exec symfony-web php bin/console lexik:jwt:generate-keypair

# Exécuter les migrations
docker exec symfony-web php bin/console doctrine:migrations:migrate --no-interaction

# (Optionnel) Charger les fixtures
docker exec symfony-web php bin/console doctrine:fixtures:load --no-interaction
```

#### Sans Docker

```bash
# Installer les dépendances
composer install

# Configurer la base de données dans .env
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/symfony"

# Créer la base de données
php bin/console doctrine:database:create

# Générer les clés JWT
php bin/console lexik:jwt:generate-keypair

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# (Optionnel) Charger les fixtures
php bin/console doctrine:fixtures:load

# Lancer le serveur
symfony serve -d
```

### 3. Installation Frontend (Vue.js)

```bash
# Aller dans le dossier frontend
cd frontend

# Installer les dépendances
npm install

# Lancer le serveur de développement
npm run dev
```

Le frontend sera accessible sur **http://localhost:5173**

## Configuration

### Backend (.env)

```bash
# Base de données
DATABASE_URL="mysql://symfony:PASSWORD@symfony-db:3306/symfony"

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_jwt_passphrase
JWT_TTL=3600

# CORS
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

# Application
APP_ENV=dev
APP_SECRET=your_app_secret
```

### Frontend (optionnel)

Créer un fichier `frontend/.env.local`:

```bash
VITE_API_URL=http://localhost:8319
```

### Configuration JWT

Les clés JWT sont générées automatiquement avec:
```bash
docker exec symfony-web php bin/console lexik:jwt:generate-keypair
```

Elles sont stockées dans `config/jwt/private.pem` et `config/jwt/public.pem`

### Configuration 2FA

L'authentification à deux facteurs utilise:
- **OTPHP** pour générer les codes TOTP
- **endroid/qr-code** pour générer les QR codes
- Compatible avec Google Authenticator, Authy, etc.

## Lancement

### Avec Docker

```bash
# Démarrer tous les services
docker-compose up -d

# Backend disponible sur: http://localhost:8319
# PHPMyAdmin sur: http://localhost:8080
# MailDev sur: http://localhost:1080
```

### Frontend

```bash
cd frontend
npm run dev
# Frontend disponible sur: http://localhost:5173
```

### Arrêter les services

```bash
# Arrêter Docker
docker-compose down

# Arrêter le frontend
# Ctrl+C dans le terminal
```

## API Endpoints

### Base URL

```
http://localhost:8319
```

### Authentification

#### Inscription
```http
POST /register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "firstname": "John",
  "lastname": "Doe"
}
```

**Réponse:**
```json
{
  "message": "User created successfully",
  "email": "user@example.com",
  "firstname": "John",
  "lastname": "Doe"
}
```

#### Connexion
```http
POST /auth
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse (sans 2FA):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "roles": ["ROLE_USER"]
  }
}
```

**Réponse (avec 2FA activé):**
```json
{
  "requires_2fa": true,
  "temp_token": "temp_eyJ0eXAiOiJKV1QiLCJhbGc...",
  "message": "Please provide your 2FA code"
}
```

### Authentification à Deux Facteurs (2FA)

#### Configuration 2FA
```http
POST /api/2fa/setup
Authorization: Bearer <token>
```

**Réponse:**
```json
{
  "secret": "JBSWY3DPEHPK3PXP",
  "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANS...",
  "provisioning_uri": "otpauth://totp/MyApp:user@example.com?secret=...",
  "message": "Scannez le QR code avec votre application d'authentification"
}
```

#### Activer 2FA
```http
POST /api/2fa/enable
Authorization: Bearer <token>
Content-Type: application/json

{
  "code": "123456"
}
```

**Réponse:**
```json
{
  "message": "2FA enabled successfully",
  "backup_codes": [
    "ABC123-DEF456",
    "GHI789-JKL012",
    ...
  ],
  "warning": "Save these backup codes in a safe place. They can only be used once and will not be shown again."
}
```

#### Vérifier code 2FA (après connexion)
```http
POST /api/2fa/verify
Content-Type: application/json

{
  "temp_token": "temp_eyJ0eXAiOiJKV1QiLCJhbGc...",
  "code": "123456"
}
```

**Réponse:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "roles": ["ROLE_USER"]
  }
}
```

### Utilisateur

#### Profil utilisateur
```http
GET /api/me
Authorization: Bearer <token>
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "user@example.com",
    "firstname": "John",
    "lastname": "Doe",
    "roles": ["ROLE_USER"],
    "createdAt": "2025-12-20 10:55:22"
  }
}
```

### Films (Movies)

#### Liste des films
```http
GET /api/v1/movies?page=1&limit=20&online=true&title=inception&search=action
```

**Paramètres:**
- `page` (default: 1) - Numéro de page
- `limit` (default: 20, max: 100) - Éléments par page
- `online` (boolean) - Filtre par statut online
- `title` (string) - Recherche dans le titre
- `search` (string) - Recherche globale (titre, description, acteurs, catégories)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Inception",
      "description": "A thief who steals corporate secrets...",
      "duration": 148,
      "releaseDate": "2010-07-16",
      "image": "inception.jpg",
      "online": true,
      "createdAt": "2025-12-20 10:55:17"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 100,
    "total_pages": 5,
    "has_next": true,
    "has_previous": false
  }
}
```

#### Détail d'un film
```http
GET /api/v1/movies/1
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Inception",
    "description": "A thief who steals corporate secrets...",
    "duration": 148,
    "releaseDate": "2010-07-16",
    "image": "inception.jpg",
    "online": true,
    "createdAt": "2025-12-20 10:55:17",
    "actors": [
      {
        "id": 1,
        "firstname": "Leonardo",
        "lastname": "DiCaprio"
      }
    ],
    "categories": [
      {
        "id": 1,
        "name": "Science Fiction"
      }
    ]
  }
}
```

#### Créer un film
```http
POST /api/v1/movies
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Inception",
  "description": "A thief who steals corporate secrets...",
  "duration": 148,
  "releaseDate": "2010-07-16",
  "image": "inception.jpg",
  "online": true,
  "actors": [1, 2],
  "categories": [1]
}
```

#### Modifier un film
```http
PUT /api/v1/movies/1
PATCH /api/v1/movies/1
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Inception - Extended Edition",
  "duration": 160
}
```

#### Supprimer un film
```http
DELETE /api/v1/movies/1
Authorization: Bearer <token>
```

### Acteurs (Actors)

#### Liste des acteurs
```http
GET /api/v1/actors?page=1&limit=20
```

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "lastname": "DiCaprio",
      "firstname": "Leonardo",
      "dob": "1974-11-11",
      "dod": null,
      "bio": "American actor...",
      "photo": "dicaprio.jpg",
      "createdAt": "2025-12-20 10:55:22"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 50,
    "total_pages": 3,
    "has_next": true,
    "has_previous": false
  }
}
```

#### Détail d'un acteur
```http
GET /api/v1/actors/1
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "lastname": "DiCaprio",
    "firstname": "Leonardo",
    "dob": "1974-11-11",
    "dod": null,
    "bio": "American actor...",
    "photo": "dicaprio.jpg",
    "createdAt": "2025-12-20 10:55:22",
    "movies": [
      {
        "id": 1,
        "name": "Inception"
      },
      {
        "id": 2,
        "name": "Titanic"
      }
    ]
  }
}
```

#### Créer un acteur
```http
POST /api/v1/actors
Authorization: Bearer <token>
Content-Type: application/json

{
  "lastname": "Cruise",
  "firstname": "Tom",
  "dob": "1962-07-03",
  "bio": "American actor and producer",
  "photo": "cruise.jpg"
}
```

#### Modifier un acteur
```http
PUT /api/v1/actors/1
PATCH /api/v1/actors/1
Authorization: Bearer <token>
Content-Type: application/json

{
  "bio": "Updated biography"
}
```

#### Supprimer un acteur
```http
DELETE /api/v1/actors/1
Authorization: Bearer <token>
```

### Commentaires (Comments)

#### Liste des commentaires d'un film
```http
GET /api/v1/comments?movie=1
```

#### Créer un commentaire
```http
POST /api/v1/comments
Authorization: Bearer <token>
Content-Type: application/json

{
  "movie": 1,
  "content": "Great movie!"
}
```

#### Modifier un commentaire
```http
PATCH /api/v1/comments/1
Authorization: Bearer <token>
Content-Type: application/json

{
  "content": "Updated comment"
}
```

#### Supprimer un commentaire
```http
DELETE /api/v1/comments/1
Authorization: Bearer <token>
```

### Upload

#### Upload d'image
```http
POST /api/v1/upload/image
Authorization: Bearer <token>
Content-Type: multipart/form-data

file: <image_file>
```

### Codes de statut HTTP

| Code | Signification |
|------|---------------|
| 200  | OK - Succès |
| 201  | Created - Ressource créée |
| 400  | Bad Request - Données invalides |
| 401  | Unauthorized - Non authentifié |
| 403  | Forbidden - Non autorisé |
| 404  | Not Found - Ressource non trouvée |
| 409  | Conflict - Conflit (ex: email déjà existant) |
| 500  | Internal Server Error - Erreur serveur |

## Structure du projet

```
wr506d_projet/
├── bin/                        # Exécutables Symfony
├── config/                     # Configuration Symfony
│   ├── jwt/                    # Clés JWT
│   ├── packages/               # Configuration des bundles
│   │   ├── security.yaml       # Configuration sécurité + 2FA
│   │   └── lexik_jwt_authentication.yaml
│   └── routes/
├── frontend/                   # Application Vue.js
│   ├── src/
│   │   ├── assets/             # Images, CSS
│   │   ├── components/         # Composants réutilisables
│   │   ├── router/             # Routes Vue Router
│   │   ├── services/
│   │   │   └── api.ts          # Client API Axios
│   │   ├── stores/             # Pinia stores
│   │   │   ├── auth.ts         # Store authentification + 2FA
│   │   │   ├── movies.ts
│   │   │   └── comments.ts
│   │   ├── views/              # Pages Vue
│   │   │   ├── LoginView.vue
│   │   │   ├── RegisterView.vue
│   │   │   ├── TwoFactorSetup.vue
│   │   │   ├── TwoFactorVerify.vue
│   │   │   ├── HomeView.vue
│   │   │   ├── MovieDetailView.vue
│   │   │   └── admin/
│   │   ├── App.vue
│   │   └── main.ts
│   ├── package.json
│   └── vite.config.ts
├── migrations/                 # Migrations Doctrine
├── public/                     # Point d'entrée web
├── src/
│   ├── Controller/
│   │   ├── AuthController.php          # Inscription/connexion
│   │   ├── TwoFactorController.php     # 2FA setup/enable
│   │   └── Api/
│   │       └── UserController.php      # Profil utilisateur
│   ├── Entity/
│   │   ├── User.php                    # Entité utilisateur + 2FA
│   │   ├── Movie.php
│   │   ├── Actor.php
│   │   └── Comment.php
│   ├── Security/
│   │   ├── CustomAuthenticator.php     # Authentificateur JWT + 2FA
│   │   └── TwoFactorAuthenticator.php  # Gestion codes 2FA
│   └── Service/
│       └── TwoFactorService.php        # Service 2FA (QR, TOTP)
├── templates/                  # Templates Twig
├── var/                        # Cache, logs
├── .env                        # Variables d'environnement
├── composer.json               # Dépendances PHP
├── docker-compose.yml          # Configuration Docker
├── setup.sh                    # Script d'installation
└── README.md                   # Ce fichier
```

## Commandes utiles

### Backend (Symfony)

```bash
# Lister les routes
docker exec symfony-web php bin/console debug:router

# Créer une migration
docker exec symfony-web php bin/console doctrine:migrations:diff

# Exécuter les migrations
docker exec symfony-web php bin/console doctrine:migrations:migrate

# Charger les fixtures
docker exec symfony-web php bin/console doctrine:fixtures:load

# Vider le cache
docker exec symfony-web php bin/console cache:clear

# Générer des clés JWT
docker exec symfony-web php bin/console lexik:jwt:generate-keypair

# Créer un utilisateur admin (si commande custom existe)
docker exec symfony-web php bin/console app:create-admin
```

### Frontend (Vue.js)

```bash
# Lancer le serveur de développement
npm run dev

# Build pour production
npm run build

# Vérifier les types TypeScript
npm run type-check

# Preview du build
npm run preview
```

### Docker

```bash
# Démarrer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f

# Reconstruire les images
docker-compose build --no-cache

# Voir les conteneurs actifs
docker ps

# Accéder au conteneur Symfony
docker exec -it symfony-web bash

# Accéder au conteneur MariaDB
docker exec -it symfony-db mysql -u symfony -pPASSWORD symfony
```

### Base de données

```bash
# Créer la base de données
docker exec symfony-web php bin/console doctrine:database:create

# Supprimer la base de données
docker exec symfony-web php bin/console doctrine:database:drop --force

# Voir le schéma SQL
docker exec symfony-web php bin/console doctrine:schema:update --dump-sql

# Mettre à jour le schéma (attention: pas pour production)
docker exec symfony-web php bin/console doctrine:schema:update --force
```

## Accès aux services

- **Application Symfony**: http://localhost:8319
- **Frontend Vue.js**: http://localhost:5173
- **PHPMyAdmin**: http://localhost:8080
  - Serveur: `db`
  - Utilisateur: `symfony`
  - Mot de passe: `PASSWORD`
- **MailDev**: http://localhost:1080

## Système de rôles

### ROLE_USER (par défaut)
- Consulter les films et acteurs
- Poster des commentaires
- Activer la 2FA
- Gérer son profil

### ROLE_ADMIN
- Toutes les permissions ROLE_USER
- Gérer tous les films et acteurs
- Modérer les commentaires
- Gérer les utilisateurs

## Sécurité

### JWT
- Les tokens JWT ont une durée de vie de 1 heure (configurable dans JWT_TTL)
- Les tokens sont stockés dans le localStorage côté frontend
- Les clés JWT sont générées automatiquement et stockées dans `config/jwt/`

### 2FA (Authentification à deux facteurs)
- Basé sur TOTP (Time-based One-Time Password)
- Compatible avec Google Authenticator, Authy, Microsoft Authenticator, etc.
- Codes de backup générés lors de l'activation (8 codes)
- Les codes de backup sont hashés en base de données
- Les secrets 2FA sont uniques par utilisateur

### Workflow 2FA
1. L'utilisateur se connecte avec email/password
2. Si 2FA activé, il reçoit un `temp_token`
3. L'utilisateur scanne le QR code avec son app d'authentification
4. L'utilisateur saisit le code à 6 chiffres
5. Le backend vérifie le code et renvoie le JWT final

## Dépannage

### Erreur "Permission denied" sur les clés JWT
```bash
docker exec symfony-web chmod 644 config/jwt/private.pem
docker exec symfony-web chmod 644 config/jwt/public.pem
```

### Erreur CORS
Vérifier la configuration dans `config/packages/nelmio_cors.yaml`:
```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['*']
        allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
        allow_headers: ['Content-Type', 'Authorization']
```

### Base de données inaccessible
```bash
# Vérifier que le conteneur est lancé
docker ps

# Voir les logs
docker-compose logs db

# Recréer le conteneur
docker-compose down
docker-compose up -d
```

### Frontend ne se connecte pas au backend
Vérifier l'URL de l'API dans `frontend/src/services/api.ts`:
```typescript
const apiClient = axios.create({
  baseURL: 'http://localhost:8319'
})
```

## Prochaines étapes

- [ ] Ajouter des tests unitaires (PHPUnit, Vitest)
- [ ] Ajouter des tests E2E (Cypress)
- [ ] Implémenter GraphQL
- [ ] Ajouter la pagination côté frontend
- [ ] Améliorer l'UX (loading states, toasts)
- [ ] Ajouter un système de notifications
- [ ] Implémenter le mode sombre
- [ ] Optimiser les performances (lazy loading, cache)

## Licence

Proprietary

## Support

Pour toute question ou problème, créer une issue sur le repository.
