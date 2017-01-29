<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model {

    protected $table="hospital";

    protected $fillable = [ 'name', 
    						'country_id',
    						'province_id',
    						'city_id',
    						'addres',
    						];

     use SoftDeletes;

    public function specialty(){
		return $this->belongsToMany('Modules\ClientService\Entities\Specialty',
										'specialty_hospital',
										'hospital_id',
										'specialty_id'); 
	}
    
    public function plan(){
		return $this->belongsToMany('Modules\Plan\Entities\Plan',
										'plan_hospital',
										'hospital_id',
										'plan_id'); 
	}

	public function emergency(){
		return $this->hasMany('Modules\ClientService\Entities\Emergency',
			                   'hospital_id','id');
	}

	public function hospitalization(){
		return $this->hasMany('Modules\ClientService\Entities\Hospitalization',
			                   'hospital_id','id');
	}
}