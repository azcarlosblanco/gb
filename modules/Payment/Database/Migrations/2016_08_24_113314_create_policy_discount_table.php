<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolicyDiscountTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_discount', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('policy_id')->unsigned();
            $table->string('concept');
            $table->float('percentage');
            $table->tinyInteger('state'); //0 - requested
                                          //1 - applied
                                          //2 - cancelled
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
        Schema::drop('');
    }

}
