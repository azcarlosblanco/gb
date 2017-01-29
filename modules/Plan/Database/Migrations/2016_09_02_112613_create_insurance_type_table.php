<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInsuranceTypeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_type', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ins_company_ins_type', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('insurance_type_id')->unsigned();
            $table->integer('insurance_company_id')->unsigned();
            $table->foreign('insurance_type_id')
                  ->references('id')->on('insurance_type')
                  ->onDelete('Cascade');
            $table->foreign('insurance_company_id')
                  ->references('id')->on('insurance_company')
                  ->onDelete('Cascade');
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
        Schema::drop('insurance_type');
    }

}
