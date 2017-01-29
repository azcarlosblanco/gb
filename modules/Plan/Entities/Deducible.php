<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\PlanCost;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deducible extends Model {

	protected $table = 'plan_deducible';

    protected $fillable = array('name','plan_id');

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
    public function plan(){
    	return $this->belongsTo('Modules\Plan\Entities\Plan','plan_id'); // this matches the Eloquent model
    }

    /**
     * Function Model:deducibleOptions
     * Description: Define the relationship between the deducible the cost of the plan related to it 
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return DeducibleOptions
     */
    public function deducibleOptions(){
    	return $this->hasMany('Modules\Plan\Entities\DeducibleOptions','plan_deducible_id'); // this matches the Eloquent model
    }

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
    	return $this->hasMany('Modules\Plan\Entities\PlanCost','plan_deducible_id'); // this matches the Eloquent model
    }

    public static function getDeduciblesReasons(){
        return ['local'=>'Local',
                  'international'=>'International',
                  'in_usa'=>'Dentro Estados Unidos',
                  'out_usa'=>'Fuera Estados Unidos'];
    }

    public function additionalCover(){
        return $this->hasMany("Modules\Plan\Entities\PlanDeducibleAdditionalCover",
                                "plan_deducible_id",
                                "id");
    }

}