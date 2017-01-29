<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialtyHospitalTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specialty_hospital', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer("specialty_id")->unsigned();
            $table->foreign('specialty_id')
                    ->references('id')->on('specialty')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->integer("hospital_id")->unsigned();
            $table->foreign('hospital_id')
                    ->references('id')->on('hospital')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
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
        Schema::drop('specialty_hospital');
    }

}
