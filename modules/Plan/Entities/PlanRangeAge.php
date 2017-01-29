<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanRangeAge extends Model {

	protected $table = 'plan_range_age';
    protected $fillable = array('start_age','end_age');
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
    	return $this->belongsTo('Modules\Plan\Entities\Plan'); // this matches the Eloquent model
    }

}