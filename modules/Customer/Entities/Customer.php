<?php namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    protected $table='customer';

	 //type determine if the agent is a 'agent' or a 'subagent'
    protected $fillable=[
      'name',
      'lastname',
      'pid_type',
      'pid_num',
      'address',
      'phone',
      'mobile',
      'fax',
      'email',
      'country_id',
      'state_id',
      'city_id'
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

    //include trashed policies
    public function policiesAll(){
      return $this->hasMany("Modules\Policy\Entities\Policy","customer_id")->withTrashed();
    }

    //
    public function policies(){
      return $this->hasMany("Modules\Policy\Entities\Policy","customer_id");
    }
}
