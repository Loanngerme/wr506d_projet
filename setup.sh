#!/bin/bash

echo "Démarrage des conteneurs Docker..."
docker-compose up -d

echo "Attente du démarrage de la base de données..."
sleep 10

echo "Installation des dépendances Composer..."
docker exec symfony-web composer install

echo "Création des migrations..."
docker exec symfony-web php bin/console doctrine:migrations:diff --no-interaction

echo "Exécution des migrations..."
docker exec symfony-web php bin/console doctrine:migrations:migrate --no-interaction

echo "Configuration terminée!"
echo ""
echo "Accès à l'application:"
echo "  - Application web: http://localhost:8319"
echo "  - PHPMyAdmin: http://localhost:8080"
echo "  - MailDev: http://localhost:1080"
echo ""
echo "Routes disponibles:"
echo "  - Créer un film: http://localhost:8319/movie/create"
echo "  - Liste des films: http://localhost:8319/movie/list"
echo "  - Mettre à jour un film: http://localhost:8319/movie/{id}/update"
echo "  - Voir les logs: http://localhost:8319/logs"
