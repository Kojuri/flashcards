<?php  
class Carte extends \Illuminate\Database\Eloquent\Model {  
  protected $table = 'carte';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function collection(){
    return $this->belongsTo('Collection', 'collection_id');
  }
}