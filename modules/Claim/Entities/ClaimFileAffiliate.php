<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JWTAuth;

class ClaimFileAffiliate extends Model {
    protected $table='claim_file_affiliate';
    protected $fillable = ['claim_file_id', 
    					'affiliate_policy_id',
    					'date_invoice',
    					'value',
    					'concept'];
    use SoftDeletes;

    public function claimFile(){
    	return $this->belongsTo('ClaimFile','claim_file_id','id');
    }

    public function affiliatePolicy(){
    	return $this->belongsTo('Modules\Affiliate\Entities','affiliate_policy_id','id');
    }

}
