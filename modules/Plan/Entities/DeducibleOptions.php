<?php namespace Modules\Plan\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Plan\Entities\Plan;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeducibleOptions extends Model {

	protected $table = 'plan_deducible_options';

    protected $fillable = array('plan_deducible_type_id','value','plan_deducible_id');

    use softDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Function Model:plan
     * Description: Define the relationship the deducible and the plan it belongs to
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return Deducible
     */
    public function deducible(){
    	return $this->belongsTo('Modules\Plan\Entities\Deducible','plan_deducible_id'); // this matches the Eloquent model
    }

    public function setValueAttribute($value){
        $this->attributes['value'] = floatval($value);
    }

    public function setReason($value){
        $this->attributes['plan_deducible_type_id'] = strtolower($value);
    }

    public function deducibleType(){
      return $this->belongsTo('Modules\Plan\Entities\PlanDeducibleType','plan_deducible_type_id');
    }

    //added reason as relation coz we added a reason table instead of a string
    public function getReasonAttribute(){
      return $this->deducibleType->name;
    }

}
