# Documentation API RESTful - Movies & Actors

## Base URL
```
http://localhost:8319/api/v1
```

## Format des réponses

Toutes les réponses sont au format JSON avec la structure suivante :

```json
{
  "success": true|false,
  "data": {...},
  "message": "...",
  "error": "..."
}
```

---

## API Actors

### 1. Liste tous les acteurs
**GET** `/actors`

**Paramètres de pagination (optionnels):**
- `page` (default: 1): Numéro de la page
- `limit` (default: 20, max: 100): Nombre d'éléments par page

**Exemple:** `/actors?page=2&limit=20`

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "lastname": "Nolan",
      "firstname": "Christopher",
      "dob": "1970-07-30",
      "dod": null,
      "bio": "British-American film director...",
      "photo": null,
      "createdAt": "2025-12-20 10:55:17"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 200,
    "total_pages": 10,
    "has_next": true,
    "has_previous": false
  }
}
```

### 2. Obtenir un acteur par ID
**GET** `/actors/{id}`

**Paramètres:**
- `id` (required): ID de l'acteur

**Réponse détaillée avec films:**
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
        "id": 2,
        "name": "The Dark Knight"
      }
    ]
  }
}
```

### 3. Créer un nouvel acteur
**POST** `/actors`

**Body:**
```json
{
  "lastname": "Cruise",
  "firstname": "Tom",
  "dob": "1962-07-03",
  "dod": null,
  "bio": "American actor and producer",
  "photo": "cruise.jpg"
}
```

**Champs:**
- `lastname` (required): Nom de famille
- `firstname` (optional): Prénom
- `dob` (optional): Date de naissance (format: YYYY-MM-DD)
- `dod` (optional): Date de décès (format: YYYY-MM-DD)
- `bio` (optional): Biographie
- `photo` (optional): URL de la photo

**Réponse:**
```json
{
  "success": true,
  "message": "Actor created successfully",
  "data": {
    "id": 3,
    "lastname": "Cruise",
    "firstname": "Tom",
    ...
  }
}
```

### 4. Mettre à jour un acteur
**PUT** `/actors/{id}` ou **PATCH** `/actors/{id}`

**Body:**
```json
{
  "bio": "American actor - Updated bio"
}
```

Tous les champs sont optionnels. Seuls les champs fournis seront mis à jour.

**Réponse:**
```json
{
  "success": true,
  "message": "Actor updated successfully",
  "data": {...}
}
```

### 5. Supprimer un acteur
**DELETE** `/actors/{id}`

**Réponse:**
```json
{
  "success": true,
  "message": "Actor deleted successfully"
}
```

---

## API Movies

### 1. Liste tous les films
**GET** `/movies`

**Paramètres de pagination (optionnels):**
- `page` (default: 1): Numéro de la page
- `limit` (default: 20, max: 100): Nombre d'éléments par page

**Paramètres de filtrage (optionnels):**
- `online` (boolean): Filtre les films par statut online (true/false)
- `title` (string): Recherche partielle dans le titre uniquement (insensible à la casse)
- `search` (string): Recherche globale dans titre, description, noms d'acteurs et catégories (insensible à la casse)

**Exemples:**
- `/movies?page=2&limit=20`
- `/movies?online=true` - Uniquement les films en ligne
- `/movies?title=inception` - Films contenant "inception" dans le titre
- `/movies?search=dicaprio` - Films avec "dicaprio" dans le titre, description, acteurs ou catégories
- `/movies?online=true&search=action&page=1&limit=10` - Films en ligne avec "action" (titre/description/acteurs/catégories)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "The Dark Knight",
      "description": "When the menace known as the Joker...",
      "duration": 152,
      "releaseDate": "2008-07-18",
      "image": "dark-knight.jpg",
      "online": true,
      "createdAt": "2025-12-20 10:55:17"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 500,
    "total_pages": 25,
    "has_next": true,
    "has_previous": false
  }
}
```

### 2. Obtenir un film par ID
**GET** `/movies/{id}`

**Réponse détaillée avec acteurs et catégories:**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Top Gun: Maverick",
    "description": "After thirty years...",
    "duration": 131,
    "releaseDate": "2022-05-27",
    "image": "topgun-maverick.jpg",
    "online": true,
    "createdAt": "2025-12-20 11:03:14",
    "actors": [
      {
        "id": 3,
        "firstname": "Tom",
        "lastname": "Cruise"
      }
    ],
    "categories": [
      {
        "id": 1,
        "name": "Action"
      }
    ]
  }
}
```

### 3. Créer un nouveau film
**POST** `/movies`

**Body:**
```json
{
  "name": "Top Gun: Maverick",
  "description": "After thirty years, Maverick is still pushing the envelope...",
  "duration": 131,
  "releaseDate": "2022-05-27",
  "image": "topgun-maverick.jpg",
  "actors": [3],
  "categories": [1]
}
```

**Champs:**
- `name` (required): Nom du film
- `description` (optional): Description
- `duration` (optional): Durée en minutes
- `releaseDate` (optional): Date de sortie (format: YYYY-MM-DD)
- `image` (optional): URL de l'image
- `online` (optional, default: false): Disponibilité en ligne (boolean)
- `actors` (optional): Tableau d'IDs d'acteurs
- `categories` (optional): Tableau d'IDs de catégories

**Réponse:**
```json
{
  "success": true,
  "message": "Movie created successfully",
  "data": {...}
}
```

### 4. Mettre à jour un film
**PUT** `/movies/{id}` ou **PATCH** `/movies/{id}`

**Body:**
```json
{
  "name": "Top Gun: Maverick - Extended Edition",
  "duration": 145,
  "online": true,
  "actors": [3, 2],
  "categories": [1, 2]
}
```

Tous les champs sont optionnels.

**Note:** Pour `actors` et `categories`, la liste fournie **remplace** complètement la liste existante.

**Réponse:**
```json
{
  "success": true,
  "message": "Movie updated successfully",
  "data": {...}
}
```

### 5. Supprimer un film
**DELETE** `/movies/{id}`

**Réponse:**
```json
{
  "success": true,
  "message": "Movie deleted successfully"
}
```

---

## Filtrage et Recherche des Films

L'API permet de filtrer et rechercher les films selon plusieurs critères.

### Filtres disponibles

#### 1. Filtre par statut online

Récupérer uniquement les films disponibles en ligne ou hors ligne:

```bash
# Films en ligne uniquement
curl "http://localhost:8319/api/v1/movies?online=true"

# Films hors ligne uniquement
curl "http://localhost:8319/api/v1/movies?online=false"
```

#### 2. Filtre par titre (title)

Recherche partielle insensible à la casse dans les titres de films uniquement:

```bash
# Tous les films contenant "inception" dans le titre
curl "http://localhost:8319/api/v1/movies?title=inception"

# Tous les films contenant "dark" dans le titre
curl "http://localhost:8319/api/v1/movies?title=dark"
```

#### 3. Recherche globale (search)

Recherche partielle insensible à la casse dans **plusieurs champs**:
- Titre du film
- Description du film
- Prénom et nom des acteurs
- Nom des catégories

```bash
# Recherche de "dicaprio" (trouve les films avec cet acteur)
curl "http://localhost:8319/api/v1/movies?search=dicaprio"

# Recherche de "action" (trouve les films de la catégorie Action ET ceux contenant "action" dans titre/description)
curl "http://localhost:8319/api/v1/movies?search=action"

# Recherche de "brad" (trouve les films avec un acteur prénommé Brad)
curl "http://localhost:8319/api/v1/movies?search=brad"
```

#### 4. Combinaison de filtres

Les filtres peuvent être combinés entre eux et avec la pagination:

```bash
# Films en ligne contenant "dark" dans le titre, page 1, 10 résultats
curl "http://localhost:8319/api/v1/movies?online=true&title=dark&page=1&limit=10"

# Films en ligne avec "action" (recherche globale)
curl "http://localhost:8319/api/v1/movies?online=true&search=action&limit=20"

# Films avec "dark" (search global) ET "knight" dans le titre
curl "http://localhost:8319/api/v1/movies?search=dark&title=knight"
```

**Notes:**
- Les métadonnées de pagination (total_items, total_pages) reflètent le nombre total de films **après application des filtres**
- Le paramètre `search` est plus large que `title` : il cherche dans tous les champs
- `title` et `search` peuvent être utilisés ensemble pour affiner les résultats

---

## Pagination

Toutes les listes (acteurs et films) supportent la pagination pour améliorer les performances.

### Paramètres

- `page` (optionnel, default: 1): Le numéro de la page souhaitée
- `limit` (optionnel, default: 20, max: 100): Le nombre d'éléments par page

### Métadonnées de pagination

Chaque réponse paginée inclut un objet `pagination` contenant:

- `current_page`: La page actuelle
- `per_page`: Le nombre d'éléments par page
- `total_items`: Le nombre total d'éléments dans la base
- `total_pages`: Le nombre total de pages
- `has_next`: Boolean indiquant s'il existe une page suivante
- `has_previous`: Boolean indiquant s'il existe une page précédente

### Exemples de pagination

**Page 1 avec limite par défaut (20):**
```bash
curl "http://localhost:8319/api/v1/actors?page=1"
```

**Page 2 avec 15 éléments par page:**
```bash
curl "http://localhost:8319/api/v1/movies?page=2&limit=15"
```

**Navigation:**
```bash
# Première page
curl "http://localhost:8319/api/v1/actors?page=1&limit=10"

# Page suivante
curl "http://localhost:8319/api/v1/actors?page=2&limit=10"

# Dernière page (basée sur total_pages de la réponse)
curl "http://localhost:8319/api/v1/actors?page=20&limit=10"
```

---

## Codes de statut HTTP

| Code | Signification |
|------|---------------|
| 200  | OK - Succès |
| 201  | Created - Ressource créée avec succès |
| 400  | Bad Request - Données invalides |
| 404  | Not Found - Ressource non trouvée |
| 500  | Internal Server Error - Erreur serveur |

---

## Exemples avec curl

### Créer un acteur
```bash
curl -X POST "http://localhost:8319/api/v1/actors" \
  -H "Content-Type: application/json" \
  -d '{
    "lastname": "Cruise",
    "firstname": "Tom",
    "dob": "1962-07-03",
    "bio": "American actor and producer"
  }'
```

### Mettre à jour un acteur
```bash
curl -X PUT "http://localhost:8319/api/v1/actors/3" \
  -H "Content-Type: application/json" \
  -d '{
    "bio": "Updated biography"
  }'
```

### Créer un film avec acteurs et catégories
```bash
curl -X POST "http://localhost:8319/api/v1/movies" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Inception",
    "description": "A thief who steals corporate secrets...",
    "duration": 148,
    "releaseDate": "2010-07-16",
    "image": "inception.jpg",
    "actors": [2],
    "categories": [2]
  }'
```

### Supprimer un film
```bash
curl -X DELETE "http://localhost:8319/api/v1/movies/1"
```

---

## Système d'événements

Toutes les modifications sur les films (création/mise à jour) déclenchent automatiquement un événement qui est enregistré dans la table `log_action`. Vous pouvez consulter ces logs via :

```
GET http://localhost:8319/logs
```

---

## GraphQL

*À venir*

GraphQL n'est pas encore implémenté dans cette version. Utilisez l'API RESTful ci-dessus pour toutes les opérations CRUD.
