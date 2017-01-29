<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model {

	   
    protected $table="doctor";

    protected $fillable = [ 'name', 
    						'pid_num',
    						'pid_type',
    						];

     use SoftDeletes;


	public function specialty(){
		return $this->belongsToMany('Modules\ClientService\Entities\Specialty',
										'specialty_doctor',
										'doctor_id',
										'specialty_id'); 
	}

    public function emergency(){
    	return $this->hasMany('Modules\ClientService\Entities\Emergency',
    		                   'doctor_id','id');
    }

    public function hospitalization(){
        return $this->hasMany('Modules\ClientService\Entities\Hospitalization',
                               'doctor_id','id');
    }
}
