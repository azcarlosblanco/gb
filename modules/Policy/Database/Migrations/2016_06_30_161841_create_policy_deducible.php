<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolicyDeducible extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_deducible', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('policy_id')->unsigned();
            $table->integer('plan_deducible_type_id')->unsigned();
            $table->float('amount')->unsigned();

            $table->foreign('policy_id')
                    ->references('id')->on('policy')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->foreign('plan_deducible_type_id')
                    ->references('id')->on('plan_deducible_type')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('policy_deducible');
    }

}
