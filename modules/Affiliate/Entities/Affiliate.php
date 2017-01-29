<?php namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Affiliate extends Model
{
	protected $table='affiliate';

	protected $fillable=[
        'name',
        'lastname',
        'pid_type',
        'pid_num',
        'dob',
        'height',
        'weight',
        'sex'
      ];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * Adjuntar accessors a toArray()
     * @var array
     */
    protected $appends = array('full_name');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function getFullNameAttribute()
    {
        return $this->name . " " . $this->lastname;
    }

    public function policy(){
        return $this->belongsToMany(
            'Modules\Policy\Entities\Policy',
            'affiliate_policy');
    }

    public function affiliatePolicy(){
        return $this->hasMany(
            'Modules\Affiliate\Entities\AffiliatePolicy',
            'affiliate_id'
            )->whereNull("dismiss_date");
    }

    public function affiliatePolicyAll(){
        return $this->hasMany(
            'Modules\Affiliate\Entities\AffiliatePolicy',
            'affiliate_id'
            );
    }

    /**
     * annex that belong to an affiliate
     * @return [type] [description]
     */
    /*public function annex(){
        return $this->hasManyThrough(
            'Modules\AffiliatePolicyAnnex\Entities\AffiliatePolicyAnnex',
            'Modules\AffiliatePolicy\Entities\AffiliatePolicy',
            'affiliate_id',
            'affiliate_policy_id');
    }*/

    /**
     * extra that belong to na affiliate
     * @return [type] [description]
     */
    /*public function extra(){
        return $this->hasManyThrough(
            'Modules\AffiliatePolicyExtra\Entities\AffiliatePolicyExtra',
            'Modules\AffiliatePolicy\Entities\AffiliatePolicy',
            'affiliate_id',
            'affiliate_policy_id');
    }*/

    /**
     * Get the annex of the current user corresponding to a ginven policy
     * @param  id policy
     * @return AffiliatePolicyAnnex collecion
     */
    public function getAnnexByPolicy($policy_id){
        return Affiliate::find($this->id)
                            ->affiliatePolicy()
                            ->where('policy_id',$policy_id)
                            ->annex;
    }

    /**
     * Get the extra (anmend and exclusions ) of the current user corresponding to a ginven policy
     * @param  id policy
     * @return AffiliatePolicyExtra collecion
     */
    public function getExtraByPolicy($policy_id){
        return Affiliate::find($this->id)
                            ->affiliatePolicy()
                            ->where('policy_id',$policy_id)
                            ->extra;
    }


	public function getAge(){
		$dob = new Carbon($this->dob);
		return $dob->age;
	}
}
