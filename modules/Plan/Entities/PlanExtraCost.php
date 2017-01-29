<?php namespace Modules\Plan\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanExtraCost extends Model {

	protected $table = 'plan_extra_cost';
    protected $fillable = array();
    use softDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}