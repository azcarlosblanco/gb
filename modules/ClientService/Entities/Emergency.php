<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clientservice\Entities\Observation; 

class Emergency extends Model {

    
    protected $table="emergency";
    protected $fillable = [ 'customer_policy_id', //policy
                            'hospital_id',//hospital
                            'doctor_id',  //doctor
                            'specialty_id',//specialty
                            'phone',
                            'hospitalized',
                            'accident',
                            'procedure_entry_id',
                            'start_date',//fecha de ingreso
                            'end_date',//fecha de alta
   							 ];
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
                                     'customer_policy_id',
                                     'id');
    }

    public function procedureEntry()
    {
        return $this->belongsTo('App\procedureEntry','procedure_entry_id','id');
    }

   public function observations(){
        return Observation::where("table_type","emergency")
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