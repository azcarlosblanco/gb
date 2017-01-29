<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementFileTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement_file', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('settlement_id')->unsigned();
            $table->integer('file_entry_id')->unsigned();

            $table->foreign('settlement_id')
                    ->references('id')->on('claim_settlement')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->foreign('file_entry_id')
                    ->references('id')->on('file_entry')
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
        Schema::drop('settlement_file');
    }

}
