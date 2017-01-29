<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeductDiscountClaimConcept extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_concept', function(Blueprint $table)
        {
            $table->float('deduct_discount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_concept', function(Blueprint $table)
        {
            $table->dropColumn('deduct_discount');
        });
    }

}
