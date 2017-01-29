<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGuiaRemisionRelation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guia_remision_relation', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('guia_remision_id')->unsigned();
            $table->string('table_name');
            $table->integer('table_id')->unsigned();
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
        Schema::drop('');
    }

}
