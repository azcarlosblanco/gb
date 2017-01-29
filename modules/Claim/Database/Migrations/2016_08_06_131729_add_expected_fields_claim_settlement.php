<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpectedFieldsClaimSettlement extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_settlement', function(Blueprint $table)
        {
          $table->float('expected_deduct');
          $table->float('expected_refund');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('claim_settlement', function(Blueprint $table)
      {
          $table->dropColumn('expected_deduct');
          $table->dropColumn('expected_refund');
      });
    }

}
