<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableNotused extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('payment')) {
    		Schema::drop('payment');
		}

		if (Schema::hasTable('cheque_payment_detail')) {
    		Schema::drop('cheque_payment_detail');
		}

		if (Schema::hasTable('transfer_payment_detail')) {
    		Schema::drop('transfer_payment_detail');
		}

		if (Schema::hasTable('bank_account_type')) {
    		Schema::drop('bank_account_type');
		}

		if (Schema::hasTable('credit_card_payment_detail')) {
    		Schema::drop('credit_card_payment_detail');
		}

		if (Schema::hasTable('deposit_payment_detail')) {
    		Schema::drop('deposit_payment_detail');
		}

		if (Schema::hasTable('credit_card_type')) {
    		Schema::drop('credit_card_type');
		}

		if (Schema::hasTable('credit_card_way_pay')) {
    		Schema::drop('credit_card_way_pay');
		}

		if (Schema::hasTable('credit_card_brand')) {
    		Schema::drop('credit_card_brand');
		}

		if (Schema::hasTable('policy_payment_discount')) {
    		Schema::drop('policy_payment_discount');
		}

		if (Schema::hasTable('policy_payment')) {
    		Schema::drop('policy_payment');
		}
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::create('', function(Blueprint $table)
		{
            $table->increments('id');

            $table->timestamps();
		});
	}

}
