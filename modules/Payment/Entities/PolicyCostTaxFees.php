<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyCostTaxFees extends Model {

    protected $fillable = [
    						"concept",
    						"value",
    						"policy_cost_id"
    					   ];
    use SoftDeletes;

    public function policyCost(){
    	return $this->belongsTo("Modules\Payment\Entities\PolicyCost",
    								"policy_cost_id");
    }

}