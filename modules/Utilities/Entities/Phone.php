<?php namespace Modules\Utilities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;
//use Modules\Utilities\Entity\EnumPhone;

class Phone extends Model
{
    protected $table = 'phone';
    use SoftDeletes;

    protected $fillable = [
    				'phone_type',
        			'number',
        			'phoneable_id',
        			'phoneable_type'
    			];

    public function phoneable()
    {
        return $this->morphTo();
    }    

   	public static function validator(array $data)
    {
        return Validator::make($data, [
            'phone_type'       => 'required',
            'number'           => 'required',
            'phoneable_id'     => 'required',
            'phoneable_type'  => 'required',
        ]);
    }

    public static function createPhone(array $data){
		/*if(!EnumPhone::isValidValue($data['phone_type'])){
			throw new \Exception("Invalid phone type", 402);
		}*/

    	return Phone::create(
					[
						"phone_type"      => $data['phone_type'],
						"number"          => $data['number'],
						"phoneable_id"    => $data['phoneable_id'],
						"phoneable_type"  => $data['phoneable_type'],
					]
				);
    }
}
