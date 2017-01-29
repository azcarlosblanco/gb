<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPaymentFileColumnInPaymentDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('credit_card_payment_detail', function(Blueprint $table)
        {
        	$table->dropForeign('credit_card_payment_detail_payment_file_foreign');
            $table->dropColumn('payment_file');
        });

        Schema::table('transfer_payment_detail', function(Blueprint $table)
        {
        	$table->dropForeign('transfer_payment_detail_payment_file_foreign');
            $table->dropColumn('payment_file');
        });

        Schema::table('cheque_payment_detail', function(Blueprint $table)
        {
        	$table->dropForeign('cheque_payment_detail_payment_file_foreign');
            $table->dropColumn('payment_file');
        });

        Schema::table('deposit_payment_detail', function(Blueprint $table)
        {
        	$table->dropForeign('deposit_payment_detail_payment_file_foreign');
            $table->dropColumn('payment_file');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::create('payment_file_column_in_payment_detail', function(Blueprint $table)
		{
            $table->increments('id');

            $table->timestamps();
		});
	}

}
