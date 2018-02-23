<?php

namespace App\models;

/**
* Collection entity class
*
*/

 
class Collection extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'collection';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function cartes(){
    return $this->hasMany( 'App\models\Carte', 'collection_id');
  }

}