<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDiagnosisEmergency extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('emergency', function(Blueprint $table)
        {
            $table->integer('diagnosis_id')->unsigned();
            $table->foreign('diagnosis_id')
                  ->references('id')->on('diagnosis')
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
        Schema::table('emergency', function(Blueprint $table)
        {

        });
    }

}
