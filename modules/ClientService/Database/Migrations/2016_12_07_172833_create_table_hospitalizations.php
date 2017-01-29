<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHospitalizations extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospitalizations', function(Blueprint $table)
        {
            $table->increments('id');

            $table->integer('type_hospitalization_id')->unsigned();
            $table->foreign('type_hospitalization_id')
                  ->references('id')->on('type_hospitalizations')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('policy_id')->unsigned();
            $table->foreign('policy_id')
                  ->references('id')->on('policy')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('doctor_id')->unsigned();
            $table->foreign('doctor_id')
                  ->references('id')->on('doctor')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('hospital_id')->unsigned();
            $table->foreign('hospital_id')
                  ->references('id')->on('hospital')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('specialty_id')->unsigned();
            $table->foreign('specialty_id')
                  ->references('id')->on('specialty')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('diagnosis_id')->unsigned();
            $table->foreign('diagnosis_id')
                  ->references('id')->on('diagnosis')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->text('process');

            $table->integer('procedure_entry_id')->unsigned();
            $table->foreign('procedure_entry_id')
                  ->references('id')->on('procedure_entry')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('ticket_id')->unsigned()->nullable();
            $table->foreign('ticket_id')
                  ->references('id')->on('ticket')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->string('form')->nullable();

            $table->string('report')->nullable();

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
        Schema::drop('hospitalizations');
    }

}
