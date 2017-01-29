<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NumberPayments extends Model {

	protected $table = 'number_payments';
    protected $fillable = array('number','name');
    use softDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Function Model:planCost
     * Description: Define the relationship between the deducible the cost of the plan related to it 
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return PlanCosts
     */
    public function planCosts(){
    	return $this->hasMany('Modules\Plan\Entities\PlanCost'); // this matches the Eloquent model
    }

}