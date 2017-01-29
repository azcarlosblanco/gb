<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuiaRemisionTable extends Migration {

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
        Schema::create('guia_remision', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date('date'); 
            $table->string('track_number');
            $table->string('reason');
            $table->integer('sender')->unsigned();
            $table->string('receiver_name');
            $table->string('receiver_address');
            $table->string('receiver_phone');
            $table->string('carrier');
            $table->string('external_track_number');
            $table->integer("foreign_id")->unsigned();
            $table->timestamps();
            $table->foreign('sender')
                    ->references('id')->on('user')
                    ->onDelete('Cascade');
        });

        Schema::create('guia_remision_item', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('guia_remision_id')->unsigned();
            $table->string('description');
            $table->integer('num_copies');
            $table->foreign('guia_remision_id')
                    ->references('id')->on('guia_remision')
                    ->onDelete('Cascade');
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
        Schema::drop('guia_remision_item');
        Schema::drop('guia_remision');
    }

}
