<?php

//API routes go here

$app->get('/collections[/]', 'CollectionController:getCollections')->setName('get_collections');

$app->get('/collection/{id: [0-9]+}[/]', 'CollectionController:getCollection')->setName('get_collection');