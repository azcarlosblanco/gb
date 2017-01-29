<?php namespace Modules\Plan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Policy\Entities\QuoteCode;
use Modules\Plan\Entities\PlanCategory;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\DeducibleOptions;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\InsuranceType;
use Modules\Plan\Entities\PlanDeducibleAdditionalCover;
use Modules\Plan\Entities\PlanDeducibleAdditionalCoverValue;

class BestDoctorsPlansTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		$ins_comp_id = 1;
		$username = 'flormariduena';
	    $password = 'Fm0914126354';
	    $base_url = 'https://agentportal.bestdoctorsinsurance.com/';
	    //file to store cookie data
	    $cookieFile = storage_path().'/cookies/bestdoctorsplan.txt';

	    $login_fields = array (
	      'Username' => $username,
	      'Password' => $password,
	      'AccountType' => 1
	      );

	    $cookieJar = new FileCookieJar($cookieFile, true);
		$client_login = new Client(['cookies' => $cookieJar]);
		$login_url = $base_url.'Account/Login';
		$client_login->request('POST', $login_url, ['query'=>$login_fields]);

		$categories_url = $base_url.'Agent/GetAllIssuerByMasterAgent';
		$cat_client = new Client(['cookies' => $cookieJar]);

		$response = $cat_client->request('POST', $categories_url, ['query'=>array('Id'=>569)]);
		$str_resp = $response->getBody()->getContents();
		$resp_class = json_decode($str_resp);//print_r($resp_class);exit;

		\DB::statement("set foreign_key_checks=0;");
		\DB::beginTransaction();
		/*\DB::statement("truncate table plan_category;");
		\DB::statement("truncate table plan;");
		\DB::statement("truncate table plan_deducible;");
		\DB::statement("truncate table plan_deducible_options;");
		\DB::statement("truncate table plan_deducible_additional_cover;");
		\DB::statement("truncate table plan_deducible_additional_cover_value;");*/
		\DB::delete("delete from quote_code where table_type='plan_category' or table_type='plan' or table_type='plan_deducible' or table_type='plan_deducbile_additional_cover' or table_type='plan_deducbile_additional_cover_value';");

		$exist = NumberPayments::where('name','annual')->first();
		if($exist!=null){
			NumberPayments::create([
				'name'        => 'annual',
	            'description' => 'Anual',
				'number'      => 1
			]);
		}
		
		$exist = NumberPayments::where('name','biannual')->first();
		if($exist!=null){
			NumberPayments::create([
				'name'   => 'biannual',
	            'description' => 'Semestral',
				'number' => 2
				]);
		}

		$exist = NumberPayments::where('name','quarterly')->first();
		if($exist!=null){
			NumberPayments::create([
				'name'   => 'quarterly',
	            'description' => 'Trimestral',
				'number' => 4
				]);
		}

		$insurance_type = InsuranceType::where('name','health')
											->first();
		if($insurance_type==null){
			$insurance_type = InsuranceType::create([
											'name'         => 'health', 
											'display_name' => 'Salud'
											]);
		}

		//print_r($resp_class->list);

		foreach( $resp_class->list as $cat ){
			$plan_cat_obj = PlanCategory::where('name',$cat->Code)->first();
			if($plan_cat_obj==null){
				$plan_cat_obj = new PlanCategory();
				$plan_cat_obj->name = $cat->Code;
				$plan_cat_obj->display_name = $cat->Code;
				$plan_cat_obj->insurance_company_id = $ins_comp_id;
				$plan_cat_obj->save();
			}
			
			QuoteCode::create([
				'table_type' => 'plan_category',
				'table_id' => $plan_cat_obj->id,
				'value' => $cat->Id,
				'insurance_company_id' => $ins_comp_id
			]);

			$country_id = 64;
			$date = "Sun, 28 Aug 2016 20:51:02 GMT";
			if( $cat->Id == 1 ){
				$agent_id = 3440;
			}
			else{
				$agent_id = 4149;
			}

			$plans_params = array(
				'agentId' => $agent_id,
				'issuerId' => $cat->Id,
				'countryId' => $country_id,
				'effectiveDate' => $date
			);

			$plans_url = $base_url.'Quote/GetAllPlanQuotes';
			$plans_client = new Client(['cookies' => $cookieJar]);

			$plans_resp = $plans_client->request('POST', $plans_url, ['query'=>$plans_params]);
			$plans_resp = $plans_resp->getBody()->getContents();
			$plans_class = json_decode($plans_resp);

			foreach($plans_class->list as $plan){
				$temp_plan = Plan::where('name',$plan->PlanName)
									->where('plan_category_id',$plan_cat_obj->id)
									->first();
				if($temp_plan==null){
					$temp_plan = Plan::create([
						'name' => $plan->PlanName,
						'description' => $plan->PlanName,
						'plan_category_id' => $plan_cat_obj->id,
						'insurance_company_id' => $ins_comp_id,
						'insurance_type_id' => $insurance_type->id,
					]);
				}			
				
				QuoteCode::create([
					'table_type' => 'plan',
					'table_id' => $temp_plan->id,
					'value' => $plan->Id,
					'insurance_company_id' => $ins_comp_id
				]);

				foreach( $plan->PlanOptionForWebs as $option ){
					$tmp_deducible = Deducible::where('name',$option->Name)
								->where('plan_id',$temp_plan->id)
								->first();
					if($tmp_deducible==null){
						$tmp_deducible = Deducible::create([
										"name" => $option->Name,
										"plan_id" => $temp_plan->id
										]);
					}

					QuoteCode::create([
						'table_type' => 'plan_deducible',
						'table_id' => $tmp_deducible->id,
						'value' => $option->Id,
						'insurance_company_id' => $ins_comp_id
					]);

					DeducibleOptions::firstOrCreate([
						'value' => $option->OutUsa,
						'plan_deducible_id' => $tmp_deducible->id,
						'plan_deducible_type_id' => 1
					]);

					DeducibleOptions::firstOrCreate([
						'value' => $option->InUsa,
						'plan_deducible_id' => $tmp_deducible->id,
						'plan_deducible_type_id' => 2
					]);

					foreach ($option->RiderForWebs as $key => $addCover) {
						$tmp_plan_add_cover = PlanDeducibleAdditionalCover::firstOrCreate([
									"name" => $addCover->Name,
									"require_all_members" => $addCover->RequiredForAllMembers,
									"plan_deducible_id" => $tmp_deducible->id,
								]);

						QuoteCode::create([
							'table_type' => $tmp_plan_add_cover->getTable(),
							'table_id' => $tmp_plan_add_cover->id,
							'value' => $addCover->Id,
							'insurance_company_id' => $ins_comp_id
						]);

						foreach ($addCover->RiderOptionForWebs as $key => $addCoverValue) {
							$tmp_padcv =  PlanDeducibleAdditionalCoverValue::firstOrCreate([
									"name" => $addCoverValue->Name,
									"value" => $addCoverValue->Value,
									"plan_deducible_addcover_id" => $tmp_plan_add_cover->id
								]);

							QuoteCode::create([
								'table_type' => $tmp_padcv->getTable(),
								'table_id' => $tmp_padcv->id,
								'value' => $addCoverValue->Id,
								'insurance_company_id' => $ins_comp_id
							]);
						}
					}
				}
			}//end foreach plans

		}//end for

		\DB::commit();
		\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}//end run

}
