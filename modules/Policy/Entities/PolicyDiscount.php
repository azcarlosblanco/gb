<?php namespace Modules\Policy\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyDiscount extends Model {

    protected $table = "policy_discount";
    protected $fillable = [
    						"policy_id",
    						"concept",
    						"percentage",
    						"state",
    						"policy_cost_id" //in which quote the discount was applied
    					];
    use SoftDeletes;

    public function policy(){
    	return $this->belongsTo("Modules\Policy\Entities\Policy","policy_id");
    }

}