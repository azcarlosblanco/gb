<?php namespace Modules\GuiaEnvio\Entities;
   
use Illuminate\Database\Eloquent\Model;

//register information of the person who transport the
//documentation
class Carrier extends Model {

	protected $table="carrier";

    protected $fillable = [
    						'type', //person,company
    						'full_name',
    						'identification',
    						'pid_type',
    						];

}