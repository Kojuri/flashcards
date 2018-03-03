<?php

//Dependency Injection goes here

$container = $app->getContainer();

//Controllers

$container['CollectionController'] = function($c){
	return new App\controllers\CollectionController($c);
};

$container['GameController'] = function($c){
	return new App\controllers\GameController($c);
};

//Services

$container['json_writer'] = function($c){
	return App\services\json\Writer::class;
};

//Global parameters

$container['public_url'] = 'http://web.flashcards.local:10085';

$container['public_path'] = __DIR__.'/../web';