<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountClaimSettlement extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_settlement', function(Blueprint $table)
        {
            $table->float('amount');
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
            $table->dropColumn('amount');
        });
    }

}
