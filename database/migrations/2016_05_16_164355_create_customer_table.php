<<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('lastname');
            $table->tinyInteger('pid_type')->unsigned();
            $table->string('pid_num');
            $table->string('address');
            $table->string('phone');
            $table->string('mobile');
            $table->string('fax')->nullable();
            $table->string('email');
            $table->integer('country_id')->unsigned();
            $table->integer('state_id')->unsigned()->nullable();
            $table->integer('city_id')->unsigned();

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
        Schema::drop('customer');
    }
}
