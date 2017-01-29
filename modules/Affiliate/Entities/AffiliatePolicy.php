<?php namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliatePolicy extends Model
{
	protected $table='affiliate_policy';

	protected $fillable=[
        'policy_id',
        'affiliate_id',
        //cuando el afiliado entra en la poliza
        'effective_date',
        //cuando el afiliado sale de la poliza
        'dismiss_date',
        //la prima a pagar por ese afiliado
        'premium_amount',

        'deductibles',
        //1 owner, 2 spouse, 3 dependents
        'role'
      ];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function policy(){
    	return $this->belongsTo('Modules\Policy\Entities\Policy','policy_id');
    }

    public function affiliate(){
    	return $this->belongsTo('Modules\Affiliate\Entities\Affiliate','affiliate_id');
    }

    public function annex(){
        return $this->hasMany('Modules\Affiliate\Entities\AffiliatePolicyAnnex',
            'affiliate_policy_id');
    }

    public function anmend(){
        return $this->hasMany(
            'Modules\Affiliate\Entities\AffiliatePolicyExtra',
            'affiliate_policy_id');
    }

	public function affRole(){
		return $this->belongsTo('Modules\Affiliate\Entities\AffiliateRole','role');
	}

    public function scopeAffiliate($query,$affiliate_ID){
        return $query->where('affiliate_id',$query,$affiliate_ID);
    }

    public function scopePolicy($query,$policy_ID){
        return $query->where('policy_id',$query,$policy_ID);
    }

	public function deducibles(){
        return $this->hasMany('Modules\Affiliate\Entities\AffiliatePolicyDeducible',
            'affiliate_policy_id');
    }

	public function claims(){
        return $this->hasMany('Modules\Claim\Entities\Claim',
            'affiliate_policy_id');
    }

    public function addCover(){
        return $this->hasMany("Modules\Affiliate\Entities\AffiliatePolicyAdditionalCover",
                                "affiliate_policy_id");
    }
}
