<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clientservice\Entities\Emergency;
use Modules\Clientservice\Entities\Hospitalization;

class Observation extends Model {

    protected $table="observation";

    protected $fillable = ['table_type',
                           'table_id',
                           'description'];

    use SoftDeletes;

    public function csEntity(){
        if($this->table_type=="emergency"){
        	return Emergency::find($this->table_id);
        }

        if($this->table_type=="hospitalization"){
          return Hospitalization::find($this->table_id);
        }        
   }

}