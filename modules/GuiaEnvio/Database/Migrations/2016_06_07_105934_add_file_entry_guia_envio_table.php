<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileEntryGuiaEnvioTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guia_remision', function ($table) 
        {
            $table->integer('file_entry_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guia_remision', function ($table) 
        {
            $table->dropColumn('file_entry_id');
        });    
    }

}
