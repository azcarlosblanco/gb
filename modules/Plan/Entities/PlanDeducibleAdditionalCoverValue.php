<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;

class PlanDeducibleAdditionalCoverValue extends Model {

	protected $table="plan_deducible_addcover_value";
    protected $fillable = [
    						"name",
    						"value",
    						"plan_deducible_addcover_id",
    					  ];

    public function addCover(){
    	return $this->belongsTo("Modules\Plan\Entities\PlanDeducibleAdditionalCover","plan_deducible_addcover_id");
    }
}