<?php namespace Modules\RRHH\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;
use App\EnumPhone;
use App\Person;
use App\Phone;
//use App\EnumAddress;
//use App\Address;

class Employee extends Model {

    protected $table = 'employee';
    use SoftDeletes;

    protected $fillable = [
    						'name',
    						'lastname',
    						'user_id',
                            'document_id'
    						];

    protected $appends = array('full_name');

    public function getFullNameAttribute()
    {
        return $this->name." ".$this->lastname;
    }

    public function user()
    {
        return $this->belongsTo('App\User',
                                        'user_id');
    }

    public function deparments()
    {
        return $this->belongsToMany('Modules\RRHH\Entities\Department',
        								'employee_department',
                                        'employee_id',
                                        'department_id')
        							->withPivot('state');
    }

    public function currentDepartment(){
    	return $this->belongsToMany('Modules\RRHH\Entities\Department',
        								'employee_department',
                                        'employee_id',
                                        'department_id')
                                    ->withPivot('state')
        							->wherePivot('state',1);
    }

    public function phones()
    {
        return $this->morphMany('App\Phone', 'phoneable');
    }

    public function address()
    {
        return $this->morphMany('App\Address','addressable');
    }

    public function currentAddress()
    {
        return $this->morphMany('App\Address','addressable')
                                ->where('state',1);
    }

    public static function createEmployee(array $data){
        $emp=Employee::create([
            'name'     => $data['name'],
            'lastname' => $data['lastname'],
            'user_id'  => $data['user_id'],
            'pid_type' => $data['type_id'],
            'document_id' => $data['document_id'],
        ]);

        $emp->deparments()->attach($data['department_id'],['state' => 1]);

        //phones
        $data['phones']=array();
        $data['phones'] = array(
                                EnumPhone::HOME=>$data['p_home'],
                                EnumPhone::CELLULAR=>$data['p_cel']
                            );

        $data['phones'] = array(
                                "home"=>$data['p_home'],
                                "cel"=>$data['p_cel']
                            );

        foreach ($data['phones'] as $key => $phone) {
            $dp=array(
                    "phone_type"      => $key,
                    "number"          => $phone,
                    "phoneable_id"    => $emp->id,
                    "phoneable_type" => 'Modules\RRHH\Entities\Employee',
                );
            Phone::createPhone($dp);
        };

        //address
        /*$da=[
                'province_id'        => $data['province_id'],
                'city_id'            => $data['city_id'],
                'street_1'           => $data['street_1'],
                'street_2'           => $data['street_2'],
                'num_house'          => $data['num_house'],
                'post_code'          => $data['post_code'],
                'references'         => $data['references'],
                'address_type'       => EnumAddress::HOME,
                'addressable_id'     => $emp->id,
                'addressable_type'   => "Modules\Utilities\Entities\Employee",
            ];
        Address::createAddress($da);*/
        return $emp;
    }

    public function updateEmployee(array $data){
        //update role
        $this->user->roles()->sync($data['roles']);

        $this->name=$data['name'];
        $this->lastname=$data['lastname'];
        $this->document_id=$data['document_id'];
        $this->pid_type=$data['type_id'];
        
        $idDep=$this->currentDepartment()->first()->id;
        if($idDep!=$data['department_id']){
            $this->deparments()
                    ->sync(
                            [$idDep=>['state' => 0],
                             $data['department_id']=>['state' => 1]],
                             true
                        );
        }

        //phones
        $phones=$this->phones()->get();
        foreach ($phones as $phone) {
            $phone->delete();
        }
        $data['phones'] = array(
                                EnumPhone::HOME=>$data['p_home'],
                                EnumPhone::CELLULAR=>$data['p_cel']
                            );
        foreach ($data['phones'] as $key => $phone) {
            $dp=array(
                    "phone_type"      => $key,
                    "number"          => $phone,
                    "phoneable_id"    => $this->id,
                    "phoneable_type" => 'Modules\RRHH\Entities\Employee',
                );
            Phone::createPhone($dp);
        };

        //address
        /*$addresses=$this->address()->get();
        foreach ($addresses as $address) {
            $address->delete();
        }
        $da=[
                'province_id'        => $data['province_id'],
                'city_id'            => $data['city_id'],
                'street_1'           => $data['street_1'],
                'street_2'           => $data['street_2'],
                'num_house'          => $data['num_house'],
                'post_code'          => $data['post_code'],
                'references'         => $data['references'],
                'address_type'       => EnumAddress::HOME,
                'addressable_id'     => $this->id,
                'addressable_type'   => "Modules\Utilities\Entities\Employee",
            ];
        Address::createAddress($da);*/
    }

}