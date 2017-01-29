<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanDeducibleAdditionalCoverValueTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_deducible_addcover_value', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->integer('plan_deducible_addcover_id')->unsigned();
            $table->float('value');
            $table->timestamps();
            $table->foreign('plan_deducible_addcover_id')
                  ->references('id')->on('plan_deducible_addcover')
                  ->onDelete('Cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('plan_deducible_additional_cover_value');
    }

}
