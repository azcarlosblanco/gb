<?php namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliatePolicyExtra extends Model
{
	protected $table='affiliate_policy_extras';

	protected $fillable=[
        'type',         //0 -> exclusion, 
                        //1 -> amend
        'description',
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

    public static function getTypeOptions(){
        return [0 => "Exclusion", 1 => "Enmienda"];
    }
}