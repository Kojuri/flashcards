<?php

//Database configuration goes here

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'flashcards',
    'database'  => 'flashcards',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
]);

$capsule->setAsGlobal();

$capsule->bootEloquent();