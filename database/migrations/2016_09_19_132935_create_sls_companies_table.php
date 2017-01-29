<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('commission_type_id')->unsigned();
            $table->timestamps();

            $table->foreign('commission_type_id')->references('id')->on('sls_commission_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_companies');
    }
}
