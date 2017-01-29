<?php namespace Modules\Reception\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestPolicyData extends Model 
{
	protected $table='request_policy_data';

	//type determine if the agent is a 'agent' or a 'subagent'
	protected $fillable=[
						 'process_id',
						 'customer_identity',
						 'customer_fullname',
						 'plan_id',
						 'agente_id',
						 'data',
						];

	//to enable soft delete in the model
    use SoftDeletes;

     /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}