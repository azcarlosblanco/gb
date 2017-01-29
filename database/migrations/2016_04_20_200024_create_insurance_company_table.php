<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInsuranceCompanyTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_company', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('company_name');
            $table->string('representative');
            $table->timestamps();
            $table->softDeletes();
        });

        //table to save general emails of the insurance company
        Schema::create('insurance_company_email', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('email');
            $table->string('contact_name');
            $table->string('reason'); //sale, support, emision, renovation, 
            $table->integer('insurance_company_id')->unsigned();
            $table->timestamps();
            $table->foreign('insurance_company_id')
                    ->references('id')->on('insurance_company')
                    ->onDelete('cascade');
            $table->softDeletes();
        });

        Schema::create('insurance_company_office', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('office_name');
            $table->string('representative');
            $table->string('email'); //contact email of the sucursal
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('address');
            $table->boolean('default'); //this is the default information we get
            $table->integer('insurance_company_id')->unsigned();
            $table->timestamps();
            $table->foreign('insurance_company_id')
                    ->references('id')->on('insurance_company')
                    ->onDelete('cascade');
            $table->softDeletes();
        });

        Schema::create('insurance_company_phone', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('number');
            $table->boolean('default');
            $table->integer('insurance_company_office_id')->unsigned();
            $table->timestamps();
            $table->foreign('insurance_company_office_id')
                    ->references('id')->on('insurance_company_office')
                    ->onDelete('cascade');
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
        Schema::drop('insurance_company');
    }

}
