<?php

//API routes go here

//Retrieves all the collections
$app->get('/collections[/]', 'CollectionController:getCollections')->setName('get_collections');

//Retrieves the collection with given id
$app->get('/collections/{id: [0-9]+}[/]', 'CollectionController:getCollection')->setName('get_collection');

//Creating a new game session
$app->post('/games[/]', 'GameController:createGame')->setName('create_game');

//Saving score
$app->patch('/games/{id: [0-9]+}/score[/]', 'GameController:sendScore')->setName('send_score');