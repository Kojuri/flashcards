<?php

namespace App\models;

/**
* Réponse entity class
*
*/

class Response extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'response';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function game(){
    return $this->belongsTo('App\models\Game', 'game_id');
  }

public function carte(){
    return $this->belongsTo('App\models\Carte', 'carte_id');
  }
}
