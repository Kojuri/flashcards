<?php

namespace App\models;

/**
* Carte entity class
*
*/

class Carte extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'carte';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function collection(){
    return $this->belongsTo('App\models\Collection', 'collection_id');
  }
}