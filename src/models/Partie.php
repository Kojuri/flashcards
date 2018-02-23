<?php

namespace App\models;

/**
* Partie entity class
*
*/


class Partie extends \Illuminate\Database\Eloquent\Model {
	
  protected $table = 'partie';
  protected $primaryKey = 'id';
  public $timestamps = true;

}