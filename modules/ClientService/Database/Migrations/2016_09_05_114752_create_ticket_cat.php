<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketCat extends Migration {

        public function up()
    {
        Schema::create('ticket_cat', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
            $table->softDeletes();
        });

         Schema::create('ticket_cat_role', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer("ticket_cat_id")->unsigned();
            $table->foreign('ticket_cat_id')
                    ->references('id')->on('ticket_cat')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->integer("role_id")->unsigned();
            $table->foreign('role_id')
                    ->references('id')->on('role')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down()
    {
        Schema::drop('ticket_cat_role');
        Schema::drop('ticket_cat');
        
    }


}
