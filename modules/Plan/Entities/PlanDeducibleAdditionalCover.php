<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;

class PlanDeducibleAdditionalCover extends Model {

	protected $table="plan_deducible_addcover";
    protected $fillable = [
    						"name",
    						"require_all_members",
    						"plan_deducible_id"
    					  ];

    public function addCoverValue(){
        return $this->hasMany("Modules\Plan\Entities\PlanDeducibleAdditionalCoverValue",
                                "plan_deducible_addcover_id",
                                "id");
    }
}