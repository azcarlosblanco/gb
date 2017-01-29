<?php namespace Modules\InsuranceCompany\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DBCompanyDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        Model::unguard();
        //$this->call(PermissionsInsuranceCompanySeeder::class);
        
		\DB::table('insurance_company')->insert(
        array(
            'id' => 1,
            'company_name' => 'Best Doctors',
            'representative' => 'Someone',
        ));

        \DB::table('insurance_company_email')->insert(
        array(
            'email' => 'general@db.com',
            'contact_name' => 'Someone',
            'reason' => 'requestPolicyInsuranceCompany',
            'insurance_company_id' => 1,
            'template' => 'Favor procesar la solicitud adjunta, vigencia <EFFECTIVE_DATE>.', 
            'subject'  => '[<TRAMITE_ID>] Solicitud <CUSTOMER>'
        ));

        \DB::table('insurance_company_email')->insert(
        array(
            'email' => 'general@db.com',
            'contact_name' => 'Someone',
            'reason' => 'sendPaymentData',
            'insurance_company_id' => 1,
            'template' => 'Estimado,
        El cliente <CUSTOMER> de la póliza con ID <POLICY_NUMBER> nos informa que va a pagar mediante <PAYMENT_METHOD> en forma <NUMBER_PAYMENTS>', 
            'subject'  => '[<TRAMITE_ID>] Forma de Pago: <CUSTOMER> <POLICY_NUMBER>'
        ));

        \DB::table('insurance_company_office')->insert(
        array(
            'id' => 1,
            'office_name' => 'Best Doctors EEUU',
            'representative' => 'Someone',
            'email' => 'hhhh@example.com',
            'country' => 'eeuu',
            'state' => 'someestate',
            'city' => 'somecity',
            'address' => 'someaddress',
            'default' => true,
            'insurance_company_id' => 1,
        ));

        \DB::table('insurance_company_phone')->insert(
        array(
            'id' => 1,
            'number' => '046038985',
            'default' => true,
            'insurance_company_office_id' => 1,
        ));

         Model::reguard();  
	}
}