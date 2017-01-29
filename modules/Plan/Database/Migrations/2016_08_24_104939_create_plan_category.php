<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanCategory extends Migration {

    /**
     * Run the migrations.
     * we will use this table to save strings like Best Doctors BDIL and PPG policies
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_category', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name');
            $table->integer('insurance_company_id')->unsigned();
            $table->foreign('insurance_company_id')
                    ->references('id')->on('insurance_company')
                    ->onDelete('Cascade');
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
        Schema::drop('plan_category');
    }

}
