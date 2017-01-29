<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospitalization extends Model {

    
    protected $table = "hospitalizations";
    protected $fillable = ['type_hospitalization_id',
                           'policy_id',
                           'doctor_id',
                           'hospital_id',
                           'specialty_id',
                           'diagnosis_id',
                           'process',
                           'procedure_entry_id',
                           'form',
                           'report'];


    use SoftDeletes;

    public function specialty(){
    	return $this->belongsTo('Modules\Clientservice\Entities\Specialty',
    		                         'specialty_id',
    		                         'id');
    }

    public function hospital() {
    	return $this->belongsTo('Modules\Clientservice\Entities\Hospital',
    		                         'hospital_id',
    		                         'id');
    }

    public function doctor(){
        return $this->belongsTo('Modules\Clientservice\Entities\Doctor',
        	                         'doctor_id',
        	                         'id');
    }

    public function policy() {
        return $this->belongsTo('Modules\Policy\Entities\Policy',
                                     'policy_id',
                                     'id');
    }

    public function procedureEntry()
    {
        return $this->belongsTo('App\procedureEntry','procedure_entry_id','id');
    }

     public function observations(){
        return Observation::where("table_type","hospitalization")
                            ->where("table_id",$this->procedure_entry_id)
                            ->get();
    }

    public function ticket()
    {
        return $this->belongsTo('Modules\Clientservice\Entities\Ticket',
                                'ticket_id',
                                'id');
    }


}