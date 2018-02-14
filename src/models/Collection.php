<?php  
class Collection extends \Illuminate\Database\Eloquent\Model {  
  protected $table = 'collection';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function cartes(){
    return $this->hasMany( 'Carte', 'collection_id');
  }
}