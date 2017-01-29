<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolicyCostDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_cost_detail', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('concept');
            $table->float('value');
            $table->integer("policy_cost_id")->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('policy_cost_id')
                        ->references('id')
                        ->on('policy_cost')
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
        Schema::drop('policy_payment_detail');
    }

}
