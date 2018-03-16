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

- Ajouter les hosts virtuels `api.flashcards.local - admin.flashcards.local - web.flashcards.local - dbadmin.flashcards.local - migrate.flashcards.local`
- Installer les dépendances du projet `$ composer update`
- Créer les services et networks docker `$ docker-compose up`
- Démarrer les conteneurs docker créés `$ docker-compose start`
- Récupérer l'id du conteneur `web.flashcards.local` avec la commande `$ docker ps`
- Exécuter le conteneur `web.flashcards.local` en bash avec la commande `$ docker exec -it {id_récupéré} /bin/bash`
- Aller sur le répértoire `/var/www/web`
- Exécuter la commande `$ chmod -R 777 ./uploads/` pour autoriser PHP à écrire sur le répértoire `/var/www/web/uploads` (Téléchargement d'images)
- Créer le schèma de la base de données et importer les données de test (fixtures) en exécutant le lien `http://migrate.flashcards.local:10083`
- Se connecter à l'espace admin sur le lien `http://admin.flashcards.local:10081`
- Un compte professeur de test est disponible, avec les identifiants `--mail=admin@flashcards.fr --mdp=admin`
