<?php

namespace Modules\Utilities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Utilities\Entities\EnumAddress;

class Address extends Model
{
 	protected $table = 'address';
    use SoftDeletes;

    protected $fillable = [
    				'province_id',
        			'city_id',
        			'street_1',
        			'street_2',
        			'num_house',
        			'post_code',
        			'state',
        			'references',
        			'address_type',
        			'addressable_id',
        			'addressable_type'
    ];

    public function isValid(){

    }

    public static function validator(array $data)
    {
        return Validator::make($data, [
            'province_id'  => 'required|exists:province,id',
            'city_id'      => 'required',
            'street_1'     => 'required',
            'street_2'     => 'required',
            'num_house'    => 'required',
            'post_code'    => 'required',
            'address_type' => 'required',
        ]);
    }

    public static function createAddress(array $data){
        if(!EnumAddress::isValidValue($data['address_type'])){
            throw new \Exception("Invalid Address type", 402); 
        }

        return Address::create(
                [
                    'province_id'        => $data['province_id'],
                    'city_id'            => $data['city_id'],
                    'street_1'           => $data['street_1'],
                    'street_2'           => $data['street_2'],
                    'num_house'          => $data['num_house'],
                    'post_code'          => $data['post_code'],
                    'references'         => $data['references'],
                    'address_type'       => $data['address_type'],
                    'addressable_id'     => $data['addressable_id'],
                    'addressable_type'   => $data['addressable_type'],
                    'state'              => 1
                ]
            );
    }
}
