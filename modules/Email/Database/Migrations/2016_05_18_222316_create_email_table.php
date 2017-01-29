<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * This table save the email address from which the
         * application send the emails for each procedures
         */
        Schema::create('email_by_reason', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('reason'); //emission, renovation, claim, general
            $table->string('sender');
            $table->string('subject');
            $table->text('template')->nullable();
            $table->text('template_html')->nullable();
            $table->integer('company_id')->unsigned();
            $table->timestamps();
        });

        /**
         * This table save the email address from which the
         * application send the emails for the different procedures
         */
        Schema::create('email_configuration', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('sender');
            $table->string('domain');
            //company_id es para identificar que a que tenant le pertenecen las opciones de configuracion
            $table->integer('company_id')->unsigned();
            $table->timestamps();
        });

        /**
         * This table save the email address from which the
         * application send the emails for the different procedures
         */
        Schema::create('email_variable', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('description');
            $table->string('value');
            //company_id es para identificar que a que tenant le pertenecen las opciones de configuracion
            $table->integer('company_id')->unsigned();
            $table->timestamps();
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('email_by_reason');
        Schema::drop('email_configuration');
        Schema::drop('email_variable');
    }

}
