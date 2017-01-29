<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTicketId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_detail', function(Blueprint $table)
        {
             $table->integer("ticket_id")->unsigned();
             $table->foreign('ticket_id')
                    ->references('id')->on('ticket')
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
        
    }

}
