<?php 

namespace Modules\Utilities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    protected $table = 'state';
    use SoftDeletes;

    protected $fillable = ['name'];


    public function cities(){
      return $this->hasMany("Modules\Utilities\Entities\City","state_id");
    }
}