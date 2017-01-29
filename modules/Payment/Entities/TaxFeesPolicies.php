<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxFeesPolicies extends Model {

    protected $fillable = [	
    						'name',
            				'type',// percentage, fee
            				'value',
            				'descrpition',
						];
						
    use SoftDeletes;

}