<?php

namespace App\controllers;

/**
* 
*/
class BaseController
{
	protected $container;
	
	function __construct($container)
	{
		$this->container = $container;
	}

	protected function get($key){
		return isset($this->container[$key])? $this->container[$key] : NULL;
	}
	
}