<?php

require __DIR__ .'/../vendor/autoload.php';
require __DIR__ .'/../src/includes/db.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\models\Professeur;
use App\models\Collection;
use App\models\Carte;

class Migrator {
	
    /**
     * migrate the database schema
     */
    public function migrate() {
        

        /**
         * create table for professeur
         */
        if (!Capsule::schema()->hasTable('professeur')) {
            Capsule::schema()->create('professeur', function($table)
            {
                $table->integer('id', true);
                $table->string('nom');
                $table->string('prenom');
                $table->string('mail');
                $table->string('mdp');
                $table->timestamp('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->timestamp('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'));
            });
        }

        /**
         * create table for collections
         */
        if (!Capsule::schema()->hasTable('collection')) {
            Capsule::schema()->create('collection', function($table)
            {

                $table->integer('id', true);
                $table->string('libelle')->default('');
                $table->string('image')->default('');

                /* Paramétrage */

                $table->boolean('min_learning_time_required')->default(false); // Indique si le temps minimal d'apprentissage est activé

                $table->boolean('max_learning_time_required')->default(false); // Indique si le temps maximal d'apprentissage est activé

                $table->integer('min_learning_time')->default(60); // Le temps minimal d'apprentissage si activé (En secondes)

                $table->integer('max_learning_time')->default(360); // Le temps maximal d'apprentissage si activé (En secondes)

                $table->boolean('max_response_time_required')->default(false); // Indique si le temps maximal pour répondre est activé

                $table->integer('max_response_time')->default(60); // Le temps maximal avant de considérer la réponse comme fausse (en secondes)

                $table->integer('nb_attempts_allowed')->default(1); // Le nombre de tentatives autorisées avant de considérer que la réponse est fausse

                $table->integer('display_type')->default(1); // Type d'affichage des cartes pour le player (Nombre de cartes par ligne pour faire en sorte de ne pas avoir une barre de défilement)

                /* FK */
                $table->integer('professeur_id');

                //Foreign keys declaration
                $table->foreign('professeur_id')->references('id')->on('professeur')->onDelete('cascade');

                // We'll need to ensure that MySQL uses the InnoDB engine to
                // support the indexes, other engines aren't affected.
                $table->engine = 'InnoDB';
            });
        }

        /**
         * create table for cartes
         */
        if (!Capsule::schema()->hasTable('carte')) {
            Capsule::schema()->create('carte', function($table)
            {
                $table->integer('id', true);
                $table->string('url_image');
                $table->string('description');
                $table->timestamp('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->timestamp('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'));
                //FK
                $table->integer('collection_id');

                $table->engine = 'InnoDB';

                //Foreign keys declaration
                $table->foreign('collection_id')->references('id')->on('collection')->onDelete('cascade');
            });
        }
		
		/**
         * create table partie
         */
        if (!Capsule::schema()->hasTable('game')) {
            Capsule::schema()->create('game', function($table)
            {
                $table->integer('id', true);
                $table->string('pseudo');
                $table->boolean('is_finished')->default(false);
                $table->integer('score')->default(0);
                $table->integer('collection_id');

                $table->timestamp('created_at')->default(Capsule::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated_at')->default(Capsule::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->timestamp('finished_at')->nullable();

                $table->engine = 'InnoDB';

                //Foreign keys declaration
                $table->foreign('collection_id')->references('id')->on('collection')->onDelete('cascade');
            });
        }

        /**
         * create table response
         */
        if (!Capsule::schema()->hasTable('response')) {
            Capsule::schema()->create('response', function($table)
            {
                $table->integer('id', true);
                $table->integer('nb_attempts'); // Nombre de tentatives
                $table->boolean('is_correct'); // Réponse correcte ou pas
                $table->integer('response_time'); // Temps de réponse (en secondes)
                $table->integer('game_id'); // La partie concernée
                $table->integer('carte_id'); // La carte concernée

                $table->engine = 'InnoDB';

                //Foreign keys declaration
                $table->foreign('game_id')->references('id')->on('game')->onDelete('cascade');

                $table->foreign('carte_id')->references('id')->on('carte')->onDelete('cascade');
            });
        }

    }
}

$migrator = new Migrator();

$migrator->migrate();


//Données par défaut

if(Professeur::count() === 0){
	//Default professeur
	$password = password_hash('admin', PASSWORD_DEFAULT);
	$professeur = Professeur::create(['nom' => 'Admin', 'prenom' => 'admin', 'mail' => 'admin@flashcards.fr', 'mdp' => $password]);

	//Default collection
	$collection = Collection::create(['libelle' => 'Animaux', 'image' => 'uploads/030c896c-a119-44b6-a96f-aabcc15e3a5a.jpg', 'professeur_id' => $professeur->id]);

	//Default cartes

	$collection->cartes()->saveMany([
		new Carte(['url_image' => 'uploads/988dc563-ce19-4d0c-9b93-04486d38577b.jpg', 'description' => 'Ane']),
		new Carte(['url_image' => 'uploads/f8cc2714-ee91-45b3-a2db-2de1d371df07.jpg', 'description' => 'Canard']),
		new Carte(['url_image' => 'uploads/2037d513-e1d0-4e85-a965-19fbbc5b5c4a.jpg', 'description' => 'Coq']),
		new Carte(['url_image' => 'uploads/a9b6fd38-8f2c-4ca3-96a2-037cf4c147eb.jpg', 'description' => 'Lapin']),
		new Carte(['url_image' => 'uploads/86d80375-54a6-4e90-9cf3-67d7355bff4c.jpg', 'description' => 'Chat']),
		new Carte(['url_image' => 'uploads/eb94f7ae-29ec-4c45-bd2e-8a2fb1f88235.jpg', 'description' => 'Cheval']),
		new Carte(['url_image' => 'uploads/f466b3f1-00c0-4c1a-93b6-e6cc2cb60994.jpg', 'description' => 'Chien']),
		new Carte(['url_image' => 'uploads/41ed6dae-5c97-405c-9855-4ae3d90ce331.jpg', 'description' => 'Cochon']),
		new Carte(['url_image' => 'uploads/ad650a03-2e9c-447d-8f4f-ba191253a613.jpg', 'description' => 'Grenouille']),
		new Carte(['url_image' => 'uploads/7593d359-9759-4dea-a0fc-76547d7ac545.jpg', 'description' => 'Poisson Rouge']),
		new Carte(['url_image' => 'uploads/fd2e6626-6e1b-48d3-acc1-fd96aae897d2.jpg', 'description' => 'Vache']),
		new Carte(['url_image' => 'uploads/d7bf912d-5902-4869-a215-65456c84705c.jpg', 'description' => 'Oie'])
	]);
}

?>

<!DOCTYPE html>
<html>

    <head>
    
        <meta charset="utf-8">
        <title>Création de la base de données</title>
        <style type="text/css">

            img
            {
                float: left; 
            }

            #display-success
            {
                width: 400px;
                border: 1px solid #D8D8D8;
                padding: 10px;
                border-radius: 5px;
                font-family: Arial;
                font-size: 11px;
                text-transform: uppercase;
                background-color: rgb(236, 255, 216);
                color: green;
                text-align: center;
                margin-top: 30px;
            }

            #display-success img
            {
                position: relative;
                bottom: 5px;
            }

        </style>
    
    </head>
    
    <body>
    
        <div id="display-success">
            <img src="img/correct.png" alt="Success" /> Le schéma de la base de données a bien été créé.
        </div>

    </body>

</html>