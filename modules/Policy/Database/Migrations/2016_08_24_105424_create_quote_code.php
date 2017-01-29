<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteCode extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_code', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('table_type');
            $table->integer('table_id')->unsigned();
            $table->string('value');
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
        Schema::drop('');
    }

}
