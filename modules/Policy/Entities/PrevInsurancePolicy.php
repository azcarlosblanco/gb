<?php namespace Modules\Policy\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrevInsurancePolicy extends Model {

	protected $table = "prev_insurance_policy";
    protected $fillable = [
    						"policy_id",
    						"company_name",
    						"plan_name"
    					];
    use SoftDeletes;

    public function policy(){
    	return $this->belongsTo("Modules\Policy\Entities\Policy","policy_id");
    }
}