<?php namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliatePolicyAnnex extends Model
{
	protected $table='affiliate_policy_annexe';

	protected $fillable=[
        'description',
        'effective_date',
        'affiliate_policy_id',
      ];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

   	public function affiliatePolicy(){
    	return $this->belongsTo('Modules\Affiliate\Entities\AffiliatePolicy',
    		'affiliate_policy_id');
    }

}