<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteDeductiblesAffiliatePolicy extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('affiliate_policy', function(Blueprint $table)
		{
			$table->dropColumn("deductibles");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('', function(Blueprint $table)
		{

		});
	}

}
