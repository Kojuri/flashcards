<?php

namespace App\models;

/**
* Game (Partie) entity class
*
*/


class Game extends \Illuminate\Database\Eloquent\Model {
	
  protected $table = 'game';
  protected $primaryKey = 'id';
  protected $fillable = ['pseudo', 'is_finished'];
  public $timestamps = true;

 public function responses(){
    return $this->hasMany( 'App\models\Response', 'game_id');
  }

}
