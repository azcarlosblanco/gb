<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmergencyTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emergency', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('customer_policy_id');
            $table->integer('hospital_id')->unsigned();
            $table->foreign('hospital_id')
                  ->references('id')->on('hospital')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->integer('doctor_id')->unsigned();
            $table->foreign('doctor_id')
                  ->references('id')->on('doctor')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->integer('specialty_id')->unsigned();
            $table->foreign('specialty_id')
                  ->references('id')->on('specialty')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->integer('phone');
            $table->integer('ticket_id')->unsigned()->nullable();
            $table->foreign('ticket_id')
                  ->references('id')->on('ticket')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->tinyinteger('hospitalized');
            $table->tinyinteger('accident');      
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
        Schema::drop('emergency');
    }

}
