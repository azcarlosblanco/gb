<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaycheckSettlementFile extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_file', function(Blueprint $table)
        {
          $table->boolean('paycheck')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('settlement_file', function ($table) {
        $table->dropColumn('paycheck');
      });
    }

}
