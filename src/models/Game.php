<?php

namespace App\models;

/**
* Game (Partie) entity class
*
*/


class Game extends \Illuminate\Database\Eloquent\Model {
	
  protected $table = 'game';
  protected $primaryKey = 'id';
  public $timestamps = true;

}