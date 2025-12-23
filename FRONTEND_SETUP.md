# Guide de Démarrage - Frontend Vue.js

## 🚀 Démarrage Rapide

### 1. Vérifier que le backend est lancé

Le backend Symfony doit être accessible sur `http://localhost:8319`

```bash
# Depuis la racine du projet
docker ps  # Vérifier que les conteneurs sont actifs
```

### 2. Installer et lancer le frontend

```bash
# Aller dans le dossier frontend
cd frontend

# Installer les dépendances
npm install

# Lancer le serveur de développement
npm run dev
```

Le frontend sera accessible sur : **http://localhost:5173**

## 📋 Fonctionnalités Implémentées

### ✅ Backend (Symfony + API Platform)

**Authentification :**
- POST `/api/register` - Inscription avec JWT
- POST `/api/login_check` - Connexion JWT
- GET `/api/me` - Profil utilisateur

**Movies (Films) :**
- GET `/api/v1/movies` - Liste paginée avec filtres
  - `?search=` - Recherche globale
  - `?title=` - Recherche par titre
  - `?online=true/false` - Filtre en ligne
  - `?author=id` - Filtre par auteur
  - `?date_from=` & `?date_to=` - Filtre par date
- GET `/api/v1/movies/:id` - Détail d'un film
- POST `/api/v1/movies` - Créer un film (authentifié)
- PATCH `/api/v1/movies/:id` - Modifier (authentifié)
- DELETE `/api/v1/movies/:id` - Supprimer (authentifié)

**Comments (Commentaires) :**
- GET `/api/v1/comments?movie=id` - Liste des commentaires
- POST `/api/v1/comments` - Créer (authentifié)
- PATCH `/api/v1/comments/:id` - Modifier (auteur/admin)
- DELETE `/api/v1/comments/:id` - Supprimer (auteur/admin)

**Users (Utilisateurs) :**
- GET `/api/v1/users` - Liste (admin)
- GET `/api/v1/users/:id` - Détail (propriétaire/admin)
- PATCH `/api/v1/users/:id` - Modifier (propriétaire/admin)
- DELETE `/api/v1/users/:id` - Supprimer (admin)
- PATCH `/api/v1/users/:id/roles` - Modifier les rôles (admin)

**Upload :**
- POST `/api/v1/upload` - Upload fichier (authentifié)
- POST `/api/v1/upload/image` - Upload image (authentifié)

### ✅ Frontend (Vue.js 3)

**Pages Publiques :**
- `/` - Liste des films avec recherche et filtres
- `/movies/:id` - Page de détail avec commentaires
- `/login` - Connexion
- `/register` - Inscription

**Pages Administration (Authentifié) :**
- `/admin/movies` - Gestion des films
- `/admin/movies/create` - Créer un film
- `/admin/movies/:id/edit` - Modifier un film
- `/admin/comments` - Modération commentaires
- `/admin/users` - Gestion utilisateurs (admin uniquement)

**Stores Pinia :**
- `authStore` - Authentification et gestion utilisateur
- `moviesStore` - CRUD films avec filtres
- `commentsStore` - Gestion des commentaires

## 🔐 Système de Rôles

### ROLE_USER (par défaut)
- Consulter les films et commentaires (public)
- Poster des commentaires (authentifié)
- Gérer ses propres films et commentaires

### ROLE_ADMIN
- Toutes les permissions ROLE_USER
- Accès à la gestion des utilisateurs
- Suppression de tous contenus/commentaires
- Modification des rôles utilisateurs

## 📁 Structure des Fichiers Frontend

```
frontend/
├── src/
│   ├── assets/              # Images, CSS globaux
│   ├── components/          # Composants réutilisables
│   ├── router/              # Configuration Vue Router
│   │   └── index.ts         # Routes + guards
│   ├── services/
│   │   └── api.ts           # Client Axios + types TypeScript
│   ├── stores/              # Pinia stores
│   │   ├── auth.ts
│   │   ├── movies.ts
│   │   └── comments.ts
│   ├── views/               # Pages
│   │   ├── HomeView.vue
│   │   ├── MovieDetailView.vue
│   │   ├── LoginView.vue
│   │   ├── RegisterView.vue
│   │   └── admin/
│   │       ├── AdminLayout.vue
│   │       ├── MoviesView.vue
│   │       ├── MovieFormView.vue
│   │       ├── UsersView.vue
│   │       └── CommentsView.vue
│   ├── App.vue              # Composant racine + navigation
│   └── main.ts              # Point d'entrée
├── package.json
└── vite.config.ts
```

## 🧪 Test de l'Application

### 1. Créer un compte admin

```bash
# Dans le conteneur Symfony
docker exec -it symfony-web bash

# Créer un utilisateur admin via console Symfony
# (si vous avez une commande pour ça)
# OU créez un utilisateur via l'API et modifiez ses rôles en BDD
```

### 2. Tester les fonctionnalités

**Partie publique :**
1. Ouvrir http://localhost:5173
2. Naviguer dans la liste des films
3. Utiliser les filtres et la recherche
4. Cliquer sur un film pour voir les détails

**Authentification :**
1. Créer un compte via "Inscription"
2. Se connecter
3. Poster un commentaire sur un film
4. Se déconnecter et vérifier que les commentaires restent visibles

**Administration :**
1. Se connecter avec un compte
2. Accéder à "Administration"
3. Créer un film
4. Modifier un film
5. Supprimer un commentaire
6. (Si admin) Gérer les utilisateurs et leurs rôles

## 🐛 Dépannage

### Erreur CORS
Si vous avez des erreurs CORS, vérifier que `nelmio_cors` est bien configuré dans le backend :

```yaml
# backend/config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['*']
        allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
        allow_headers: ['Content-Type', 'Authorization']
```

### Token JWT expiré
Si le token expire, il faut se reconnecter. Le token est stocké dans `localStorage`.

### Routes non trouvées
Vérifier que le serveur de développement Vue.js est bien lancé et que l'URL du backend est correcte dans `src/services/api.ts`.

## 📝 Variables d'Environnement

Si besoin de variables d'environnement :

```bash
# frontend/.env.local
VITE_API_URL=http://localhost:8319/api
```

Puis utiliser dans le code :
```typescript
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8319/api'
})
```

## 🎯 Prochaines Étapes

1. **Améliorer l'upload d'images**
   - Intégrer l'upload dans le formulaire de film
   - Prévisualisation avant upload
   - Gestion des miniatures

2. **Ajouter la gestion des acteurs/réalisateurs**
   - Pages CRUD pour actors et directors
   - Sélection multiple dans le formulaire film

3. **Tests**
   - Tests unitaires avec Vitest
   - Tests E2E avec Cypress

4. **Optimisations**
   - Lazy loading des images
   - Cache des requêtes API
   - Debounce sur les recherches

5. **UX/UI**
   - Mode sombre
   - Animations de transition
   - Loading skeletons
   - Toast notifications
