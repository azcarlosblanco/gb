<?php namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Payment\Entities\BankAccountType;
use Modules\Payment\Entities\CreditCardBrand;
use Modules\Payment\Entities\CreditCardType;
use Modules\Payment\Entities\CreditCardWayPay;

class PaymentDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		
		Model::unguard();

		$this->call("\Modules\Payment\Database\Seeders\PaymentPermissionsSeeder");
		$this->call("\Modules\Payment\Database\Seeders\PaymentMethodSeeder");

		//bank_accoun_type
		BankAccountType::create(array("display_name"=>"Ahorros"));
		BankAccountType::create(array("display_name"=>"Corriente"));

		//credit_card_type
		CreditCardType::create(array("display_name"=>"Coorporativa"));
		CreditCardType::create(array("display_name"=>"Personal"));

		//credit_card_brand
		CreditCardBrand::create(array("display_name"=>"American Express"));
		CreditCardBrand::create(array("display_name"=>"Visa"));
		CreditCardBrand::create(array("display_name"=>"Mastercard"));
		CreditCardBrand::create(array("display_name"=>"Diners Club"));
		CreditCardBrand::create(array("display_name"=>"Otros"));

		//credit_card_way_pay
		CreditCardWayPay::create(array("display_name"=>"Diferido"));
		CreditCardWayPay::create(array("display_name"=>"Con Interesés"));
		CreditCardWayPay::create(array("display_name"=>"Corriente"));
		CreditCardWayPay::create(array("display_name"=>"Sin Interés"));
		CreditCardWayPay::create(array("display_name"=>"3 Meses"));
		CreditCardWayPay::create(array("display_name"=>"6 Meses"));
		CreditCardWayPay::create(array("display_name"=>"9 Meses"));
		CreditCardWayPay::create(array("display_name"=>"12 Meses"));
	}

}
