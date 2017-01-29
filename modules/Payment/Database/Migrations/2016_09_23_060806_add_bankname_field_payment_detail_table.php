<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBanknameFieldPaymentDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('transfer_payment_detail', function(Blueprint $table)
        {
            $table->string('bank_name')->change();
        });

        Schema::table('cheque_payment_detail', function(Blueprint $table)
        {
            $table->string('bank_name')->change();
        });

        Schema::table('deposit_payment_detail', function(Blueprint $table)
        {
            $table->string('bank_name')->change();
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
