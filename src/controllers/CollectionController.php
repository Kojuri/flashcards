<?php

namespace App\controllers;

use App\models\Collection;
use App\models\Professeur;

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

        return $this->get('json_writer')::json_output($response,201,$result);
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

            $time_rules = array('min_learning_time' => null, 'max_learning_time' => null);

            if(!isset($request_body['min_learning_time_required']) && !isset($request_body['max_learning_time_required'])) {

                $collection->min_learning_time_required = false;
                $collection->min_learning_time_required = false;
                $collection->save();

            }else{

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

                $collection->save();

                $this->get('flash')->addMessage('messages' ,'Hello world');

                return $response->withRedirect($this->get('router')->pathFor('edit_rules_page', array('id' => $collection->id)));
            }

                

        }
        catch(ModelNotFoundException $ex) {

        }
    }

}