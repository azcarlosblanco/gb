<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolicyCostTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_cost', function(Blueprint $table)
        {
            $table->increments('id');
            $table->float('total'); //value pay
            $table->date('date_paidoff');
            $table->integer('state'); //0 paidoff, 1 pending , 2 cancelled
            $table->integer('policy_id'); 
            $table->integer('quote_number');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('policy_payment');
    }

}
