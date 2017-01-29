<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewal extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @description :: se agregan varios campos,
     * id, id de la entidad
     */
    public function up()
    {
        Schema::create('renewal', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('policy_id')->unsigned();
            $table->integer('number_registry')->unsigned();
            $table->integer('number_policy')->unsigned();
            $table->string('name_client');

            $table->dateTime('date_renewal');
            $table->integer('state_policy');

            $table->foreign('policy_id')
                  ->references('id')
                  ->on('policy')
                  ->onUpdate('cascade')->onDelete('restrict');



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
        Schema::drop('renewal');
    }

}
