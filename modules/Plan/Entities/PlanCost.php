<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\DeducibleOptions;
use Modules\Plan\Entities\Plan;


class PlanCost extends Model {

	protected $table = 'plan_cost';
    protected $fillable = array(
    						'value',
    						'start_age',
                            'end_age',
    						'plan_deducible_id',
    						'plan_type_id',
    						'number_payments_id'
    						);
    use softDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Function Model:decucibles
     * Description: Define the relationship the deducible and the plan it belongs to 
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return Deducible
     */
    public function numberPayments(){
    	return $this->belongsTo('Modules\Plan\Entities\NumberPayments'); // this matches the Eloquent model
    }

    /**
     * Function Model:decucibles
     * Description: Define the relationship the deducible and the plan it belongs to 
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return Deducible
     */
    public function planType(){
    	return $this->belongsTo('Modules\Plan\Entities\PlanType'); // this matches the Eloquent model
    }

    /**
     * Function Model:decucibles
     * Description: Define the relationship the deducible and the plan it belongs to 
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return Deducible
     */
    public function deducibles(){
    	return $this->belongsTo('Modules\Plan\Entities\Deducible','plan_deducible_id'); // this matches the Eloquent model
    }

    /**
     * Get all costs of the plan given a deducible
     */
    public function plan()
    {
        return $this->hasManyThrough('Modules\Plan\Entities\Plan',
                                        'Modules\Plan\Entities\Deducible',
        								'plan_deducible_id',
                                        'plan_id');
    }

    /*public function createUsingRange($input,PlanRangeAge $rangeAge){

    }*/

}