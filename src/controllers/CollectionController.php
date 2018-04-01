<?php

namespace App\controllers;

use App\models\Collection;
use App\models\Professeur;
use App\models\Carte;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

use \Illuminate\Database\Eloquent\ModelNotFoundException;

class CollectionController extends BaseController
{
	public function getCollections ($request, $response) {

		$result = array();
		$collections = Collection::select()->get();
		foreach ($collections as $collection) {
            // if ($serie->cartes()->count()>=10)
            // {
                $result_temp =  $collection;

                $result_temp->image = $this->get('public_url').DIRECTORY_SEPARATOR.$collection->image;

                $result_temp->professeur = Professeur::find($result_temp->professeur_id);
                unset($result_temp->professeur_id);
                unset($result_temp->professeur->mdp);

                $result_temp->cartes = $collection->cartes()->get();

                foreach ($result_temp->cartes as $key => $carte) {
                    $result_temp->cartes[$key]->url_image = $this->get('public_url').DIRECTORY_SEPARATOR.$carte->url_image;
                }

                array_push($result,$result_temp);
            // }
            
        }

        return $this->get('json_writer')::json_output($response, 201, $result);
    }

    public function getCollection ($request, $response, $args) {

        try {

            $collection = Collection::findOrFail($args['id']);
            $collection->image = $this->get('public_url').DIRECTORY_SEPARATOR.$collection->image;

            $collection->professeur = Professeur::find($collection->professeur_id);
            unset($collection->professeur_id);
            unset($collection->professeur->mdp);

            foreach ($collection->cartes as $key => $carte) {
                $collection->cartes[$key]->url_image = $this->get('public_url').DIRECTORY_SEPARATOR.$carte->url_image;
            }

            return $this->get('json_writer')::json_output($response,201,$collection);

        } catch(ModelNotFoundException $ex) {

            return $this->get('json_writer')::json_output($response, 404, array('type' => 'error', 'message' => 'Ressource not found'));

        }

    }

    //Editing rules page request handler
    public function editRulesPage($request, $response, $args) {
        try {
            $collection = Collection::findOrFail($args['id']);

            return $this->get('view')->render($response, 'edit_rules.twig', ['collection' => $collection]);
        }
        catch(ModelNotFoundException $ex) {

        }
    }

    //Post request handler for editing collection rules
    public function editRules($request, $response, $args) {
        try {
            $collection = Collection::findOrFail($args['id']);

            $request_body = $request->getParsedBody();

            $time_rules = array('min_learning_time' => null, 'max_learning_time' => null, 'max_response_time' => null);

            if(!isset($request_body['min_learning_time_required']) 
                && !isset($request_body['max_learning_time_required']) 
                && !isset($request_body['max_response_time_required'])) {

                $collection->min_learning_time_required = false;
                $collection->max_learning_time_required = false;
                $collection->max_response_time_required = false;

            } else {

                if(isset($request_body['min_learning_time_required']) && (bool)$request_body['min_learning_time_required'] === TRUE) {
                    if(
                        isset($request_body['min_hours']) && (int)$request_body['min_hours'] >= 0
                        && isset($request_body['min_minutes']) && (int)$request_body['min_minutes'] >= 0
                        && isset($request_body['min_seconds']) && (int)$request_body['min_seconds'] >= 0
                    ){

                        $time_rules['min_learning_time'] = (int)$request_body['min_hours'] * 3600;
                        $time_rules['min_learning_time'] += (int)$request_body['min_minutes'] * 60;
                        $time_rules['min_learning_time'] += (int)$request_body['min_seconds'];

                    }else{
                        return $this->get('view')->render($response, 'edit_rules.twig', [
                            'error' => 'Les valeurs d\'entrée ne sont pas valides',
                            'collection' => $collection
                        ]);
                    }
                }else{
                    $collection->min_learning_time_required = false;
                }

                if(isset($request_body['max_learning_time_required']) && (bool)$request_body['max_learning_time_required'] === TRUE) {

                    if(
                        isset($request_body['max_hours']) && (int)$request_body['max_hours'] >= 0
                        && isset($request_body['max_minutes']) && (int)$request_body['max_minutes'] >= 0
                        && isset($request_body['max_seconds']) && (int)$request_body['max_seconds'] >= 0
                    ){

                        $time_rules['max_learning_time'] = (int)$request_body['max_hours'] * 3600;
                        $time_rules['max_learning_time'] += (int)$request_body['max_minutes'] * 60;
                        $time_rules['max_learning_time'] += (int)$request_body['max_seconds'];

                    }else{
                        return $this->get('view')->render($response, 'edit_rules.twig', [
                            'error' => 'Les valeurs d\'entrée ne sont pas valides',
                            'collection' => $collection
                        ]);
                    }
                }else{
                    $collection->max_learning_time_required = false;
                }

                if($time_rules['min_learning_time'] != null || $time_rules['max_learning_time'] != null){

                    if($time_rules['max_learning_time'] === 0) {
                        return $this->get('view')->render($response, 'edit_rules.twig', [
                            'error' => "La durée maximale d'apprentissage est trop courte",
                            'collection' => $collection
                        ]);
                    }

                    if($time_rules['min_learning_time'] != null && $time_rules['max_learning_time'] != null && $time_rules['max_learning_time'] < $time_rules['min_learning_time']) {
                        return $this->get('view')->render($response, 'edit_rules.twig', [
                            'error' => "La durée minimale d'apprentissage est supérieure à la durée maximale",
                            'collection' => $collection
                        ]);
                    }

                    if($time_rules['min_learning_time'] != null) {
                        $collection->min_learning_time_required = true;
                        $collection->min_learning_time = $time_rules['min_learning_time'];
                    }

                    if($time_rules['max_learning_time'] != null) {
                        $collection->max_learning_time_required = true;
                        $collection->max_learning_time = $time_rules['max_learning_time'];
                    }
                }

                // La durée maximale pour répondre

                if(isset($request_body['max_response_time_required']) && (bool)$request_body['max_response_time_required'] === TRUE) {
                    if(
                        isset($request_body['max_response_hours']) && (int)$request_body['max_response_hours'] >= 0
                        && isset($request_body['max_response_minutes']) && (int)$request_body['max_response_minutes'] >= 0
                        && isset($request_body['max_response_seconds']) && (int)$request_body['max_response_seconds'] >= 0
                    ){

                        $time_rules['max_response_time'] = (int)$request_body['max_response_hours'] * 3600;
                        $time_rules['max_response_time'] += (int)$request_body['max_response_minutes'] * 60;
                        $time_rules['max_response_time'] += (int)$request_body['max_response_seconds'];

                    }else{
                        return $this->get('view')->render($response, 'edit_rules.twig', [
                            'error' => 'Les valeurs d\'entrée ne sont pas valides',
                            'collection' => $collection
                        ]);
                    }
                } else {
                    $collection->max_response_time_required = false;
                }

                if($time_rules['max_response_time'] != null) {
                    $collection->max_response_time_required = true;
                    $collection->max_response_time = $time_rules['max_response_time'];
                }

            }

            // Nombre de tentatives autorisées

            if(isset($request_body['nb_attempts_allowed']) && is_numeric($request_body['nb_attempts_allowed']) && $request_body['nb_attempts_allowed'] > 0) {

                $collection->nb_attempts_allowed = filter_var($request_body['nb_attempts_allowed'], FILTER_SANITIZE_NUMBER_INT);

            }else {
                return $this->get('view')->render($response, 'edit_rules.twig', [
                    'error' => 'Les valeurs d\'entrée ne sont pas valides',
                    'collection' => $collection
                ]);
            }

            // Nombre de question par jeu

            if(isset($request_body['nb_game_questions']) && is_numeric($request_body['nb_game_questions']) && $request_body['nb_game_questions'] > 0) {

                $cartes_count = $collection->cartes->count();
                if($request_body['nb_game_questions'] > $cartes_count) {
                    return $this->get('view')->render($response, 'edit_rules.twig', [
                        'error' => 'Le nombre de questions à poser ne peut pas être supérieur au nombre total des cartes',
                        'collection' => $collection
                    ]);
                }
                $collection->nb_game_questions = filter_var($request_body['nb_game_questions'], FILTER_SANITIZE_NUMBER_INT);

            }else {
                return $this->get('view')->render($response, 'edit_rules.twig', [
                    'error' => 'Les valeurs d\'entrée ne sont pas valides',
                    'collection' => $collection
                ]);
            }

            // Affichage de l'information correct après une mauvaise réponse

            if(isset($request_body['display_correct_answer']) && (bool)$request_body['display_correct_answer'] === TRUE) {

                $collection->display_correct_answer = true;

            }else{

                $collection->display_correct_answer = false;

            }

            // Type d'évaluation

            if(isset($request_body['evaluation_type']) && is_numeric($request_body['evaluation_type']) && in_array((int)$request_body['evaluation_type'], [1, 2])){

                $collection->evaluation_type = (int)$request_body['evaluation_type'];

            }else{

                return $this->get('view')->render($response, 'edit_rules.twig', [
                    'error' => 'Les valeurs d\'entrée ne sont pas valides',
                    'collection' => $collection
                ]);

            }

            // Nombre de possibilités par question

            if(isset($request_body['nb_possible_answers']) && is_numeric($request_body['nb_possible_answers']) && (int)$request_body['nb_possible_answers'] > 1){

                $collection->nb_possible_answers = (int)$request_body['nb_possible_answers'];

            }else{

                return $this->get('view')->render($response, 'edit_rules.twig', [
                    'error' => 'Les valeurs d\'entrée ne sont pas valides',
                    'collection' => $collection
                ]);

            }




            $collection->save();

            $this->get('flash')->addMessage('messages' ,'Les modifications ont bien été enregistrées');

            return $response->withRedirect($this->get('router')->pathFor('edit_rules_page', array('id' => $collection->id)));

        }
        catch(ModelNotFoundException $ex) {
            return $response->withRedirect($this->get('router')->pathFor('not_found'));
        }
    }

	 public function duplicateCollectionPage($request, $response, $args) {
    	 if(isset($_SESSION['mail'])){
		    try {
		        $collection = Collection::findOrFail($args['id']);

		        return $this->get('view')->render($response, 'duplicateCollection.html', ['collection' => $collection]);
		    }
		    catch(ModelNotFoundException $ex) {

		    }
		}
		else{
		    header("Location: ".$this->get('router')->pathFor('accueil'));
		    exit();
		}
		
    }

	 public function duplicateCollection($request, $response, $args) {
		if(isset($_SESSION['mail'])){
		     try {
		        $oldCollection = Collection::findOrFail($args['id']);
			
				$prof = Professeur::where('mail', '=', $_SESSION['mail'])->firstOrFail();

		        $data = $request->getParsedBody();

		        if(!empty($data['libelle']) && !empty($_FILES['image']) && !empty($_FILES['image']['tmp_name']))
		        {
		            $libelle = filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS);          
		            $collection = new Collection();
		            $collection->libelle = $libelle;
		            $collection->professeur_id = $prof->id;

		            //Collection image processing

		            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
		            $filename = $_FILES["image"]["name"];
		            $filetype = $_FILES["image"]["type"];
		            $filesize = $_FILES["image"]["size"];

		            // Verify file extension
		            $ext = pathinfo($filename, PATHINFO_EXTENSION);
		            if(!array_key_exists($ext, $allowed)) {
		                return $this->view->render($response, 'ajouterCollection.html', [
		                    'error' => 'Erreur, le type de votre fichier ne correspond pas à une image !'
		                ]);
		            }

		            // Verify file size - 5MB maximum
		            $maxsize = 5 * 1024 * 1024;
		            if($filesize > $maxsize) {
		                return $this->view->render($response, 'ajouterCollection.html', [
		                    'error' => 'Erreur, la taille de votre image est trop importante. 5MB maximum'
		                ]);
		            }

		            $uuid4 = Uuid::uuid4();
		            $path = $uuid4->toString();

		            // Verify MYME type of the file
		            if(in_array($filetype, $allowed)){
		                // Check whether file exists before uploading it
		                if(file_exists($this->get('public_path."/uploads/".$path'))){
		                    return $this->view->render($response, 'ajouterCollection.html', [
		                        'error' => 'Erreur lors de l\'upload de votre image, veuillez réessayer ultérieurement.'
		                    ]);
		                } else{                 
	 						move_uploaded_file($_FILES["image"]["tmp_name"], $this->get('public_path').DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR."$path.$ext");
		                } 
		            } else{
		                return $this->get('view')->render($response, 'ajouterCollection.html', [
		                    'error' => 'Erreur lors de l\'upload de votre image, veuillez réessayer ultérieurement.'
		                ]);
		            }

		            $collection->image = 'uploads'.DIRECTORY_SEPARATOR.$path.".".$ext;

					//$collection->cartes() = $oldCollection->cartes->get();
					//$collection->cartes()->saveMany($oldCollection->cartes()->get());
					//var_dump($collection->id);
				
		
		            //Saving the new collection model
		            $collection->save();

					$oldCartes = $oldCollection->cartes()->get();
					foreach($oldCartes as $carte){
						$nvCarte = new Carte();
						$nvCarte->description = $carte->description;
						$nvCarte->url_image = $carte->url_image;
						$nvCarte->collection_id = $collection->id;
						$nvCarte->save();
					}

		            header("Location: ".$this->get('router')->pathFor('get_collection', array('id' => $collection->id)));
		            exit();
		        }
		        else{
		            return $this->get('view')->render($response, 'duplicateCollection.html', [
		                'error' => 'Veuillez remplir tous les champs !',
						'collection' => $oldCollection
		            ]);
		        }
		    }
		    catch(ModelNotFoundException $ex) {
                return $response->withRedirect($this->get('router')->pathFor('not_found'));
		    }
		}
		else{
		    header("Location: ".$this->get('router')->pathFor('accueil'));
		    exit();
		}
       
    }

    public function importCardsPage($request, $response, $args) {

        try{
            $collection = Collection::findOrFail($args['id']);

            return $this->get('view')->render($response, 'import_cards.twig', array('collection' => $collection));
        }
        catch(ModelNotFoundException $ex) {
            return $response->withRedirect($this->get('router')->pathFor('not_found'));
        }
    }


    public function importCards ($request, $response, $args) {

        try{
            $collection = Collection::findOrFail($args['id']);

            $uploadedFiles = $request->getUploadedFiles();

            if(!$uploaded_file = $uploadedFiles['csv']) {
                return $this->get('view')->render($response, 'import_cards.twig', [
                    'error' => 'Aucun fichier',
                    'collection' => $collection
                ]);
            }

            $extension = pathinfo($uploaded_file->getClientFilename(), PATHINFO_EXTENSION);

            if($extension !== 'csv') {
                return $this->get('view')->render($response, 'import_cards.twig', [
                    'error' => 'Extension de fichier non valide. Un fichier .csv est requis',
                    'collection' => $collection
                ]);
            }

            $uuid = Uuid::uuid4();
            $basename = $uuid->toString();

            $filename = $this->get('public_path').DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR."$basename.$extension";
            $uploaded_file->moveTo($filename);

            // Extraction des données depuis le fichier .csv

            $handler = fopen($filename, 'r');
            $data = null;
            $row = 1;
            $cartes = [];
            $image_data = '';
            $description = '';
            $errors = [];
            while (($data = fgetcsv($handler, 1000, ",")) !== FALSE) {
                if(count($data) != 2)
                    continue;
                if($row == 1) {
                    $row ++;
                    continue;
                }

                $description = filter_var($data[0], FILTER_SANITIZE_SPECIAL_CHARS);

                if(empty($description)){
                    array_push($errors, "Ligne $row: La description n'est pas valide");
                    continue;
                }

                try {
                    $image_data = file_get_contents($data[1]);
                    if(($image_data = imagecreatefromstring($image_data)) === FALSE) {
                        array_push($errors, "Ligne $row: L'url choisi ne renvoie pas une image valide");
                        continue;
                    }
                    $uuid = Uuid::uuid4();
                    $basename = $uuid->toString();
                    imagepng($image_data, $this->get('public_path').DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR."$basename.png");
                    imagedestroy($image_data);

                    array_push($cartes, new Carte(['description' => $description, 'url_image' => 'uploads'.DIRECTORY_SEPARATOR."$basename.png"]));
                }
                catch(Exception $ex) {
                    array_push($errors, "Ligne $row: Erreur lors du chargement de l'image");
                }

                
                $row ++;

            }

            if(count($errors) == 0) {
                $collection->cartes()->saveMany($cartes);

                $this->get('flash')->addMessage('messages' ,count($cartes).' cartes ont été ajoutées à la collection');
                
                return $response->withRedirect($this->get('router')->pathFor('get_collection', array('id' => $collection->id)));
            }
            
            return $this->get('view')->render($response, 'import_cards.twig', array(
                'collection' => $collection,
                'errors' => $errors ));

        }

        catch(ModelNotFoundException $ex) {
            return $response->withRedirect($this->get('router')->pathFor('not_found'));
        }

    }

}
