<?php

try{

	require __DIR__.'/../vendor/autoload.php';

	$app = new \Slim\App([
		'settings' => [
			'displayErrorDetails' => true
		]
	]);

	$app->add(function($request, $response, callable $next){
		$response = $response->withHeader('Content-type', 'application/json; charset=utf-8');
		$response = $response->withHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Authorization");
		$response = $response->withHeader('Access-Control-Allow-Origin', '*');
		$response = $response->withHeader('Access-Control-Allow-Methods', 'OPTION, GET, POST, PUT, PATCH, DELETE');
		return $next($request, $response);
	});


	require __DIR__.'/../src/includes/db.php';
	require __DIR__.'/../src/includes/api/dependencies.php';
	require __DIR__.'/../src/includes/api/routes.php';

	$app->run();
}
catch(RuntimeException $e){
	header('Content-type: application/json');
	header('status: 500');
	echo json_encode(array('error' => 'Internal Server Error'));
}
