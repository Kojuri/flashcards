<?php  
return [  
  'determineRouteBeforeAppMiddleware' => false,
  'outputBuffering' => false,
  'displayErrorDetails' => true,
  'db' => [
    'driver' => 'mysql',
    'host' => 'flashcards',
    'port' => '',
    'database' => 'flashcards',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => 'fc_'
  ]
];
