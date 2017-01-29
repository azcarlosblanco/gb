<?php namespace Modules\Affiliate\Entities;
   
use Illuminate\Database\Eloquent\Model;

class AffiliatePolicyAdditionalCover extends Model {

	protected $table='affiliate_policy_addcoverv';
    protected $fillable = [
    					"effective_date",
    					"affiliate_policy_id",
    					"pd_acv_id"
     					  ];

    public function additionalCoverV(){
     	return $this->belongsTo("Modules\Plan\Entities\PlanDeducibleAdditionalCoverValue","pd_acv_id");
    }

    public function affiliatePolicy(){
    	return $this->belongsTo("Modules\Affiliate\Entities\affiliatePolicy","affiliate_policy_id");
    }

}