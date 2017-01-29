<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyCostDetail extends Model {

    protected $table = "policy_cost_detail";
    protected $fillable = [
    						"concept",
    						"value",
    						"policy_cost_id",
                            "compute_value",
                            "commissionable", //1, use this value to calculation of comission
                            "isdiscount"   //1 -> this value correspond to a discount 
    					   ];
    use SoftDeletes;

    public function policyCost(){
    	return $this->belongsTo("Modules\Payment\Entities\PolicyCost",
    								"policy_cost_id");
    }

}