<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceType extends Model {

	protected $table = "insurance_type";

    protected $fillable = ["name",
    						"display_name"];

    use SoftDeletes;

    public function plan(){
    	return $this->hasMany("Modules\Plan\Entities\Plan",
                                "insurance_type_id");
    }
}