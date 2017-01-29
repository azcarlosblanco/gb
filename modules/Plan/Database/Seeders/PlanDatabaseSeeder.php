<?php namespace Modules\Plan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\PlanType;

class PlanDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        Model::unguard();
        //$this->call("\Modules\Plan\Database\Seeders\PermissionsPlanSeeder");
        //$this->call("\Modules\Plan\Database\Seeders\PlanCategoryTableSeeder");
        //$this->call("\Modules\Plan\Database\Seeders\PlanTypeTableSeeder");
        $this->call("\Modules\Plan\Database\Seeders\BestDoctorsPlansTableSeeder");
	}
}
