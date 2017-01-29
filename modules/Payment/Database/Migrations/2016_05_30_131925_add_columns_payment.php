<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsPayment extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_method', function ($table) 
        {
            $table->integer('state'); //pagado, pendiente de pago
            $table->integer('policy_id'); 
            $table->integer('payment_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_method', function ($table) 
        {
            
        });
    }

}
