<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrevInsurancePolicyTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prev_insurance_policy', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('policy_id')->unsigned();
            $table->string('company_name');
            $table->string('plan_name');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('policy_id')
                    ->references('id')->on('policy')
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
        Schema::drop('prev_insurance_policy');
    }

}
