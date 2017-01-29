<?php namespace Modules\Observation\Entities;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model {

    protected $table = 'observation';

    protected $fillable = ['process_id', 'item_id',
                           'item_ref', 'content',
                           'type_id', 'status'
                          ];

    public function type(){
      return $this->belongsTo('Modules\Observation\Entities\ObservationType','type_id','id');
    }

}
