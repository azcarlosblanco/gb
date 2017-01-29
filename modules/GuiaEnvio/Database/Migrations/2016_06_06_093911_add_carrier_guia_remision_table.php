<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCarrierGuiaRemisionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guia_remision', function ($table) 
        {
            $table->dropColumn('carrier');
            $table->integer('carrier_id')->unsigned();
            $table->foreign('carrier_id')
                    ->references('id')->on('carrier')
                    ->onDelete('Restrict');
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
            
        });
    }
}
