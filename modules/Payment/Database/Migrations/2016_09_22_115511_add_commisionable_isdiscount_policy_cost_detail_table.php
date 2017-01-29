<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddCommisionableIsdiscountPolicyCostDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy_cost_detail', function(Blueprint $table)
        {
            $table->tinyInteger('isdiscount')->default(0);
            $table->tinyInteger('commissionable')->default(1);
        });

        \DB::table("policy_cost_detail")
                ->where('concept','Admin Fee')
                ->orWhere('concept','Tax (SSC)')
                ->orWhere('concept','Tax (IVA)')
                ->update(['commissionable'=>0]);
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
