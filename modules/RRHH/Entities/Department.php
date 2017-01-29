<?php namespace Modules\RRHH\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model {

    protected $table = 'department';
    use SoftDeletes;

    /**
     * Adjuntar accessors a toArray()
     * @var array
     */
    protected $appends = array('full_name');

    protected $fillable = [
    						'name',
    						'description'
    						];

    public function employees()
    {
        return $this->belongsToMany('Modules\RRHH\Entities\Employee',
        								'employee_department',
                                        'department_id',
                                        'employee_id')
        							->withPivot('state');
    }

    public function currentEmployees(){
    	return $this->belongsToMany('Modules\RRHH\Entities\Employee',
        								'employee_department',
                                        'department_id',
                                        'employee_id')
        							->wherePivot('state',1);
    }

    
}