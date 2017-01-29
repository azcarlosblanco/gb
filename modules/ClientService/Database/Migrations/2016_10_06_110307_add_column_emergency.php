<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEmergency extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emergency', function(Blueprint $table)
        {
            $table->integer('customer_policy_id')->unsigned();
            $table->foreign('customer_policy_id')
                  ->references('id')->on('policy')
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
