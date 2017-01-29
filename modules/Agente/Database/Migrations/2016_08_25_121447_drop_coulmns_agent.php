<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCoulmnsAgent extends Migration {

	public function up()
    {
        Schema::table('agente', function(Blueprint $table)
        {
          $table->dropColumn('country');
          $table->dropColumn('city');
          $table->dropColumn('province');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function(Blueprint $table)
        {

        });
    }
}
