<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmisionRenewalNumberPolicyCostTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy_cost', function(Blueprint $table)
        {
            $table->integer("emision_number");
            $table->integer("renewal_number");
            $table->integer("user_regiter_cost")->unsigned();
            $table->smallInteger("custom_cost"); //0 - false , 1 true
            $table->foreign('user_regiter_cost')
                        ->references('id')
                        ->on('user')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policy_cost', function(Blueprint $table)
        {

        });
    }

}
