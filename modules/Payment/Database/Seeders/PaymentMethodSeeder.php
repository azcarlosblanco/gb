<?php namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Payment\Entities\PaymentMethod;

class PaymentMethodSeeder extends Seeder {


	public function run()
	{
     
     //payment method

	    $pm=PaymentMethod::where("method","cheque")->first();
	    if($pm==null)
	        PaymentMethod::create(array('method'=>'cheque','display'=>'Cheque'));
		
    	$pm=PaymentMethod::where("method","transfer")->first();
	    if($pm==null)
    	    PaymentMethod::create(array('method'=>'transfer','display'=>'Transferencia'));
		
	    $pm=PaymentMethod::where("method","deposit")->first();
	    if($pm==null)
		    PaymentMethod::create(array('method'=>'deposit','display'=>'Depósito Bancario'));
		
    	$pm=PaymentMethod::where("method","creditcard")->first();
	    if($pm==null)
		    PaymentMethod::create(array('method'=>'creditcard','display'=>'Tarjeta de Crédito'));
	}

}