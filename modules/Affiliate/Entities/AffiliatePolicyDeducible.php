<?php namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliatePolicyDeducible extends Model {

    protected $table = 'affiliate_policy_deducible';
    protected $fillable = ['plan_deducible_type_id', 'affiliate_policy_id', 'amount'];

    public function planDeducibleType(){
      return $this->belongsTo('Modules\Plan\Entities\PlanDeducibleType','plan_deducible_type_id','id');
    }

}
