<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnTicket extends Migration {

	public function up()
    {
        Schema::table('ticket', function(Blueprint $table)
        {
          $table->dropColumn('end_date');
          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket', function(Blueprint $table)
        {

        });
    }

}
