<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('state_id')->unsigned();
            $table->softDeletes();
            $table->foreign('state_id')
                        ->references('id')->on('state')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('city');
    }
}
