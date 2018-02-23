<?php

namespace App\models;

/**
* Professeur entity class
*
*/

 
class Professeur extends \Illuminate\Database\Eloquent\Model {

       protected $table      = 'professeur';  
       protected $primaryKey = 'id';     
       public    $timestamps = false;   					
											
}