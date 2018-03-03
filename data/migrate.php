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
                $table->integer('min_learning_time')->default(60);
                $table->integer('max_learning_time')->default(360);
                //FK
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

        // /**
        //  * create table palier
        //  */
        // if (!Capsule::schema()->hasTable('palier')) {
        //     Capsule::schema()->create('palier', function($table)
        //     {
        //         $table->integer('id', true);
        //         $table->integer('coef');
        //         $table->integer('points');

        //         //FK
        //         $table->integer('serie_id');

        //         $table->engine = 'InnoDB';

        //         //Foreign keys declaration
        //         $table->foreign('serie_id')->references('id')->on('serie')->onDelete('cascade');
        //     });
        // }

        // /**
        //  * create table temps
        //  */
        // if (!Capsule::schema()->hasTable('temps')) {
        //     Capsule::schema()->create('temps', function($table)
        //     {
        //         $table->integer('id', true);
        //         $table->integer('nb_seconds');
        //         $table->integer('coef');

        //         //FK
        //         $table->integer('serie_id');

        //         $table->engine = 'InnoDB';

        //         //Foreign keys declaration
        //         $table->foreign('serie_id')->references('id')->on('serie')->onDelete('cascade');
        //     });
        // }

    }
}

$migrator = new Migrator();

$migrator->migrate();


//Default data

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

header('Content-type: application/json');

echo json_encode(array('message' => 'Le schéma de la base de données a bien été créé'));