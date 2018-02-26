<?php

namespace App\models;

/**
* Professeur entity class
*
*/

 
class Professeur extends \Illuminate\Database\Eloquent\Model {

       protected $table      = 'professeur';  
       protected $primaryKey = 'id';
       protected $fillable = ['nom', 'prenom', 'mail', 'mdp'];
       public    $timestamps = false;

       public function collections() {
       		return $this->hasMany('App\models\Collection', 'professeur_id');
       }
											
}