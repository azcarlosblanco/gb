<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_method', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('method');
            $table->timestamps();
        });

        Schema::create('payment', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('payment_method_id')->unsigned();
            $table->float('amount');
            $table->date('date');
            $table->smallInteger('file_uploaded'); //1 to indicate a file was updated, 0 otherwise
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
        Schema::drop('payment');
        Schema::drop('payment_method');
    }

}
