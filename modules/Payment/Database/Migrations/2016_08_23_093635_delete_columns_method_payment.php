<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteColumnsMethodPayment extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payment_method', function(Blueprint $table)
		{
			$table->dropColumn("state");
			$table->dropColumn("policy_id");
			$table->dropColumn("payment_number");
			$table->softDeletes();
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
