<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxFeesPoliciesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_fees_policies', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('type'); // percentage, fee
            $table->float('value');
            $table->string('descrpition');
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
        Schema::drop('tax_fees_policies');
    }

}
