# Flashcards (Backoffice + API)

Application réalisée dans le cadre du projet tutoré, LP CISIIE

## Réalisée Par

- Corentin LUX
- Mohamed ALHASNE
- Gerardo Gutierrez
- Thomas Pascuzzo
- Gerardo Razo Jabana

## Pré-requis

- PHP 7+
- Apache
- docker-compose
- composer

## Installation

- Ajouter les hosts virtuels `api.flashcards.local - admin.flashcards.local - web.flashcards.local - dbadmin.flashcards.local`
- Installer les dépendances du projet `$ composer update`
- Créer les conteneur docker `$ sudo docker-compose create`
- Démarrer les conteneurs docker créés `$ sudo docker-compose start`
- Aller sur l'interface Adminer sur le lien `http://dbadmin.flashcards.local:8082`
- Se connecter avec `host=flashcards, username=root, password:root` et selectionner la base de données `flashcards`
- Importer le schèma de la base de données ainsi que les données de test (fixtures) en exécutant le fichier `./flashcards.sql`
- Se connecter à l'espace admin sur le lien `http://admin.flashcards.local:10081`
- La racine des URIs de l'API est `http://api.flashcards.local:10080`
