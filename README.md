# Projet Symfony - Système d'événements personnalisés

Ce projet démontre l'utilisation d'événements personnalisés dans Symfony pour logger automatiquement les sauvegardes d'entités.

## Architecture

### Entités

1. **Movie** (`src/Entity/Movie.php`)
   - Entité principale avec les propriétés : title, description, releaseYear, director
   - Chaque sauvegarde (création ou mise à jour) déclenche un événement

2. **LogAction** (`src/Entity/LogAction.php`)
   - Enregistre toutes les actions de sauvegarde
   - Propriétés : entityType, entityId, action, createdAt, metadata

### Système d'événements

1. **EntitySavedEvent** (`src/Event/EntitySavedEvent.php`)
   - Événement personnalisé déclenché lors de la sauvegarde d'une entité Movie
   - Contient l'entité sauvegardée et le type d'action (persist/update)

2. **DoctrineEventSubscriber** (`src/EventSubscriber/DoctrineEventSubscriber.php`)
   - Écoute les événements Doctrine `postPersist` et `postUpdate`
   - Déclenche l'événement personnalisé `EntitySavedEvent` pour les entités Movie

3. **EntitySavedListener** (`src/EventListener/EntitySavedListener.php`)
   - Écoute l'événement `EntitySavedEvent`
   - Crée une entrée dans la table LogAction avec les détails de l'action

### Contrôleurs

1. **MovieController** (`src/Controller/MovieController.php`)
   - `/movie/create` - Crée un nouveau film
   - `/movie/list` - Liste tous les films
   - `/movie/{id}/update` - Met à jour un film existant

2. **LogActionController** (`src/Controller/LogActionController.php`)
   - `/logs` - Affiche tous les logs d'actions

## Installation

### Prérequis

- Docker et Docker Compose installés

### Démarrage rapide

1. Exécutez le script de configuration :

```bash
./setup.sh
```

Ce script va :
- Démarrer les conteneurs Docker
- Installer les dépendances Composer
- Créer et exécuter les migrations de base de données

### Ou manuellement

1. Démarrez les conteneurs Docker :

```bash
docker-compose up -d
```

2. Installez les dépendances :

```bash
docker exec symfony-web composer install
```

3. Créez les migrations :

```bash
docker exec symfony-web php bin/console doctrine:migrations:diff
```

4. Exécutez les migrations :

```bash
docker exec symfony-web php bin/console doctrine:migrations:migrate
```

## Utilisation

### Accès aux services

- **Application web** : http://localhost:8319
- **PHPMyAdmin** : http://localhost:8080 (utilisateur: symfony, mot de passe: PASSWORD)
- **MailDev** : http://localhost:1080

### Routes disponibles

#### Créer un film
```
GET http://localhost:8319/movie/create
```

#### Lister les films
```
GET http://localhost:8319/movie/list
```

#### Mettre à jour un film
```
GET http://localhost:8319/movie/1/update
```

#### Voir les logs d'actions
```
GET http://localhost:8319/logs
```

## Test du système d'événements

1. Créez un film :
   - Visitez : http://localhost:8319/movie/create
   - Cela créera un film et déclenchera un événement

2. Vérifiez les logs :
   - Visitez : http://localhost:8319/logs
   - Vous verrez une entrée avec l'action "persist"

3. Mettez à jour le film :
   - Visitez : http://localhost:8319/movie/1/update
   - Cela mettra à jour le film et déclenchera un autre événement

4. Vérifiez à nouveau les logs :
   - Visitez : http://localhost:8319/logs
   - Vous verrez une nouvelle entrée avec l'action "update"

## Structure des fichiers

```
.
├── src/
│   ├── Controller/
│   │   ├── MovieController.php
│   │   └── LogActionController.php
│   ├── Entity/
│   │   ├── Movie.php
│   │   └── LogAction.php
│   ├── Event/
│   │   └── EntitySavedEvent.php
│   ├── EventListener/
│   │   └── EntitySavedListener.php
│   ├── EventSubscriber/
│   │   └── DoctrineEventSubscriber.php
│   └── Repository/
│       ├── MovieRepository.php
│       └── LogActionRepository.php
├── docker-compose.yml
├── setup.sh
└── README.md
```

## Commandes utiles

### Arrêter les conteneurs
```bash
docker-compose down
```

### Voir les logs des conteneurs
```bash
docker-compose logs -f web
```

### Accéder au conteneur web
```bash
docker exec -it symfony-web bash
```

### Créer une nouvelle migration
```bash
docker exec symfony-web php bin/console doctrine:migrations:diff
```

### Exécuter les migrations
```bash
docker exec symfony-web php bin/console doctrine:migrations:migrate
```

## Fonctionnement détaillé

Lorsqu'une entité Movie est sauvegardée :

1. Doctrine déclenche un événement `postPersist` ou `postUpdate`
2. Le `DoctrineEventSubscriber` intercepte cet événement
3. Il crée un `EntitySavedEvent` personnalisé et le dispatche
4. Le `EntitySavedListener` reçoit cet événement
5. Il crée une nouvelle entrée `LogAction` avec :
   - Le type d'entité (Movie)
   - L'ID de l'entité
   - L'action effectuée (persist ou update)
   - La date et l'heure
   - Les métadonnées (titre, réalisateur, année)
6. La `LogAction` est sauvegardée en base de données

Cette architecture permet de logger automatiquement toutes les modifications sans avoir à ajouter du code de logging dans chaque contrôleur.
