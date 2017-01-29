<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('monthly_fee');
            $table->decimal('percentage_extra');
            $table->integer('parent_seller_id')->unsigned()->nullable();
            $table->integer('commission_type_id')->unsigned();
            $table->timestamps();

            $table->foreign('commission_type_id')->references('id')->on('sls_commission_types');
            $table->foreign('parent_seller_id')->references('id')->on('sls_sellers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_sellers');
    }
}
