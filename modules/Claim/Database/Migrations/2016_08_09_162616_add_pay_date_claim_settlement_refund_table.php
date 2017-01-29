<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayDateClaimSettlementRefundTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_settlement_refund', function(Blueprint $table)
        {
          $table->date('pay_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_settlement_refund', function(Blueprint $table)
        {
            $table->dropColumn('pay_date');
        });
    }

}
