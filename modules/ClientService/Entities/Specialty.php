<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model {

    protected $table="specialty";

    protected $fillable = [	'name', 
    						'display_name',
    						];

   
	public function doctor(){
			return $this->belongsToMany('Modules\ClientService\Entities\Doctor',
										'specialty_doctor',
										'specialty_id',
										'doctor_id'); 
	}
	public function hospital(){
			return $this->belongsToMany('Modules\ClientService\Entities\Hospital',
										'specialty_hospital',
										'specialty_id',
										'hospital_id'); 
	}

	public function emergency(){
		return $this->hasMany('Module\ClientService\Entities\Emergency',
			                   'specialty_id','id');
	}

	public function hoapitalization(){
		return $this->hasMany('Module\ClientService\Entities\Hospitalization',
			                   'specialty_id','id');
	}
}