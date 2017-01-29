<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyDiscount extends Model {

	const PAYCHEQUE   = "paycheque";
	const PAYTRANSFER = "paytransfer";

	protected $table = 'policy_discount';

    protected $fillable = [
    						"policy_id",
    						"concept",
    						"percentage",
    						"state"  //0 - requested
                                     //1 - applied
                                     //2 - cancelled
    					  ];
    use SoftDeletes;

    public function policyPayment(){
    	return $this->belongsTo("Modules\Payment\Entities\Policy",
    							"policy_id")
    }

}