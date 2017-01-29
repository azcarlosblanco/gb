<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcedureEntryEmergency extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emergency', function(Blueprint $table)
        {
            $table->integer('procedure_entry_id')->unsigned();
            $table->foreign('procedure_entry_id')
                  ->references('id')->on('procedure_entry')
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
