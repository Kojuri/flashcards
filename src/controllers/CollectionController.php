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

}