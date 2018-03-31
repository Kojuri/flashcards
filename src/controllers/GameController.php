<?php

namespace App\controllers;

use App\models\Game;
use App\models\Collection;
use App\models\Response;
use App\models\Carte;

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
            unset($collection->cartes);
            // $collection->cartes = $collection->cartes()->get();

            $cartes = Carte::where('collection_id', '=', $collection->id)->inRandomOrder()->limit($collection->nb_game_questions)->get();

            $random_cartes = array();

            foreach ($cartes as $key => $carte) {
                $carte->url_image = $this->get('public_url').DIRECTORY_SEPARATOR.$carte->url_image;
                array_push($random_cartes, $carte);
            }
            $collection->image = $this->get('public_url').DIRECTORY_SEPARATOR.$collection->image;
            $collection->cartes = $random_cartes;

            $game->collection = $collection;
            $game->responses = array();

    		return $this->get('json_writer')::json_output($response, 201, $game);

    	}
    	catch(ModelNotFoundException $ex){

    		return $this->get('json_writer')::json_output($response, 404, array('type' => 'error', 'message' => 'Ressource not found'));

    	}
    	catch(\Throwable $ex){

    		return $this->get('json_writer')::json_output($response, 500, array('type' => 'error', 'message' => $ex));

    	}

    }


 public function getGamesCollection($request, $response, $args) {
        try {
            $collection = Collection::findOrFail($args['id']);
        
            return $this->get('view')->render($response, 'games.html', array(
            'collection' => $collection));
        }
        catch(ModelNotFoundException $ex) {

        }
}

    public function getGameCollection($request, $response, $args) {
        try {
            $collection = Collection::findOrFail($args['collection_id']);
        	$game = Game::findOrFail($args['game_id']);
            return $this->get('view')->render($response, 'game.html', array(
            'collection' => $collection,
			'game' => $game
			));
        }
        catch(ModelNotFoundException $ex) {

        }
    }

    public function sendScore($request, $response, $args){

        try {

            $game = Game::findOrFail($args['id']);

            if($game->is_finished == true) {
                return $this->get('json_writer')::json_output($response, 400, array('message' => 'Ce jeu est déjà terminé'));
            }

            $body = $request->getParsedBody();

            $responses = $body['game']['responses'];

            $temp = null;
            $carte = null;
        
            $final_score = 0;
        
            foreach ($responses as $key => $response_recieved) {
                 $temp = new Response();
                 $temp->is_correct = $response_recieved["is_correct"];
                 $temp->nb_attempts = $response_recieved["nb_attempts"];
                 $temp->response_time = $response_recieved["response_time"];
            
            
                 $carte = Carte::findOrFail($response_recieved['carte']['id']);
            
                 $temp->carte_id = $carte->id;
                 $temp->game_id = $game->id;
            
                 if($temp->is_correct == true) {
                     $final_score ++;
                 }
            
                 //$temp = $temp->toArray();
                 $responses[$key] = $temp->toArray();
            }
        
            $game->score = $final_score;
            $game->is_finished = true;
            $game->finished_at = new \Datetime();
            $game->save();

            // var_dump($responses);
            // exit();
        
            Response::insert($responses);

            return $this->get('json_writer')::json_output($response, 200, $game);

        } catch (ModelNotFoundException $ex) {

        }

    }

}
