<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('table_type');
            $table->integer('table_id');
            $table->integer('ticket_cat_id')->unsigned();
            $table->foreign('ticket_cat_id')
                  ->references('id')->on('ticket_cat')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->integer('policy_id')->unsigned();
            $table->foreign('policy_id')
                  ->references('id')->on('policy')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('short_desc');
            $table->integer('responsible_id')->unsigned();
            $table->foreign('responsible_id')
                  ->references('id')->on('user')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('ticket_detail',function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                  ->references('id')->on('user')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->text('comment');
            $table->tinyinteger('type');
            $table->timestamps();
        });
        
        Schema::create('ticket_attach',function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('file_entry_id')->unsigned();
            $table->foreign('file_entry_id')
                  ->references('id')->on('file_entry')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->string('description');
            $table->integer('ticket_detail_id')->unsigned();
            $table->foreign('ticket_detail_id')
                  ->references('id')->on('ticket_detail')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ticket_attach');
        Schema::drop('ticket_detail');
        Schema::drop('ticket');
    }

}
