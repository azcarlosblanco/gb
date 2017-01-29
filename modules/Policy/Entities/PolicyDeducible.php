<?php namespace Modules\Policy\Entities;

use Illuminate\Database\Eloquent\Model;

class PolicyDeducible extends Model {

    protected $table = 'policy_deducible';
    protected $fillable = ['policy_id', 'plan_deducible_type_id', 'amount'];

}
