<?php namespace Modules\Plan\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanCategory extends Model {

    protected $table = 'plan_category';
    protected $fillable = ['name', 'display_name', 'insurance_company_id'];
    public $timestamps = false;
    use softDeletes;

}
