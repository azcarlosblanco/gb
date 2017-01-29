<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatePaymentDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_card_payment_detail', function(Blueprint $table)
        {
            $table->tinyInteger('state');
            $table->integer('payment_file')->unsigned()->nullable();
            $table->foreign('payment_file')
                        ->references('id')
                        ->on('file_entry')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::table('transfer_payment_detail', function(Blueprint $table)
        {
            $table->tinyInteger('state');
            $table->integer('payment_file')->unsigned()->nullable();
            $table->foreign('payment_file')
                        ->references('id')
                        ->on('file_entry')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::table('cheque_payment_detail', function(Blueprint $table)
        {
            $table->tinyInteger('state');
            $table->integer('payment_file')->unsigned()->nullable();
            $table->foreign('payment_file')
                        ->references('id')
                        ->on('file_entry')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::table('deposit_payment_detail', function(Blueprint $table)
        {
            $table->tinyInteger('state');
            $table->integer('payment_file')->unsigned()->nullable();
            $table->foreign('payment_file')
                        ->references('id')
                        ->on('file_entry')
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
        Schema::table('', function(Blueprint $table)
        {

        });
    }

}
