<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeoGuiaEnvioTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guia_remision', function ($table) 
        {
            $table->string('receiver_country')->nullable();
            $table->string('receiver_city')->nullable();
            $table->string('receiver_post_code')->nullable();
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
            $table->dropColumn('receiver_country');
            $table->dropColumn('receiver_city');
            $table->dropColumn('receiver_post_code');
        });
    }

}
