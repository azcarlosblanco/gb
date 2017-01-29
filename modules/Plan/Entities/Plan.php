<?php namespace Modules\Plan\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model {

	protected $table = 'plan';
    protected $fillable = array('name','description','insurance_company_id', 'plan_category_id');
    use softDeletes;

	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

	/**
     * Function Model:decucibles
     * Description: Define the relationship one to many between Plan and the deducibles options
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return Deducible
     */
    public function deducibles(){
    	return $this->hasMany('Modules\Plan\Entities\Deducible','plan_id'); // this matches the Eloquent model
    }

    /**
     * Function Model:rangeAges
     * Description: Define the relationship one to many between Plan and range ages that applies to the plan
     * Date created: 29-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return PlanRangeAge
     */
    public function rangeAges(){
    	return $this->hasMany('Modules\Plan\Entities\PlanRangeAge'); // this matches the Eloquent model
    }

    /**
     * Get all costs of the plan through deducible relationship
     */
    public function planCosts()
    {
        return $this->hasManyThrough('Modules\Plan\Entities\PlanCost',
                                        'Modules\Plan\Entities\Deducible',
        								'plan_id',
                                        'plan_deducible_id');
    }

    public function insuranceCompany(){
        return $this->belongsTo('Modules\InsuranceCompany\Entities\InsuranceCompany',
            'insurance_company_id'); // this matches the Eloquent model
    }

    public function category(){
        return $this->belongsTo('Modules\Plan\Entities\PlanCategory','plan_category_id');
    }
   
    public function hospital(){
            return $this->belongsToMany('Modules\ClientService\Entities\Hospital',
                                        'plan_hospital',
                                        'plan_id',
                                        'hospital_id'); 
    }

    public function insuranceType(){
        return $this->belongsTo("Modules\Plan\Entities\insuranceType",
                                    "insurance_type_id");
    }

    /**
     * Scope a query to only include plan of a type.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTypeInsurance($query,$type)
    {
        return $query->where('insurance_type_id', $type);
    }
}
