<?php

namespace App\controllers;

use App\models\Game;
use App\models\Collection;

use \Illuminate\Database\Eloquent\ModelNotFoundException;

class GameController extends BaseController
{
	public function getGames ($request, $response) {

		$result = array();
		$games = Game::select()->get();
		foreach ($games as $key => $game) {
            $game->collection = ["id" => $game->collection_id];
            unset($game->collection_id);
            $games[$key] = $game;
        }

        return $this->get('json_writer')::json_output($response, 200, $games);
    }

    public function getGame ($request, $response, $args) {

        try {

            $game = Game::findOrFail($args['id']);
            $game->collection = ["id" => $game->collection_id];
            unset($game->collection_id);

            return $this->get('json_writer')::json_output($response, 200, $game);

        } catch(ModelNotFoundException $ex) {

            return $this->get('json_writer')::json_output($response, 404, array('type' => 'error', 'message' => 'Ressource not found'));

        }

    }

    public function createGame($request, $response, $args) {

    	$request_body = $request->getParsedBody();

    	try {

    		$collection = Collection::findOrFail($request_body['collection']['id']);
    		$game_data_arr = ['pseudo' => filter_var($request_body['pseudo'], FILTER_SANITIZE_STRING),
                "is_finished" => false];
    		$game = $collection->games()->create($game_data_arr);
            unset($game->collection_id);

            $collection->cartes = $collection->cartes()->get();
            foreach ($collection->cartes as $key => $carte) {
                $collection->cartes[$key]->url_image = $this->get('public_url').DIRECTORY_SEPARATOR.$carte->url_image;
            }
            $collection->image = $this->get('public_url').DIRECTORY_SEPARATOR.$collection->image;

            $game->collection = $collection;

    		return $this->get('json_writer')::json_output($response, 201, $game);

    	}
    	catch(ModelNotFoundException $ex){

    		return $this->get('json_writer')::json_output($response, 404, array('type' => 'error', 'message' => 'Ressource not found'));

    	}
    	catch(\Throwable $ex){

    		return $this->get('json_writer')::json_output($response, 500, array('type' => 'error', 'message' => $ex));

    	}

    }

}