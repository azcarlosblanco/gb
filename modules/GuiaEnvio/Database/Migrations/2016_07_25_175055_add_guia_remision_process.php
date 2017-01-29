<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGuiaRemisionProcess extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guia_remision_process', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('guia_remision_id')->unsigned();
            $table->foreign('process_id')
                    ->references('id')->on('process_entry')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->foreign('guia_remision_id')
                    ->references('id')->on('guia_remision')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->softDeletes();
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
        Schema::drop('guia_remision_process');
    }

}
