<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanDeducibleAdditionalCoverTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_deducible_addcover', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->integer('plan_deducible_id')->unsigned();
            $table->tinyInteger('require_all_members'); //
            $table->timestamps();
            $table->foreign('plan_deducible_id')
                  ->references('id')->on('plan_deducible')
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
        Schema::drop('plan_deducible_additional_cover');
    }

}
