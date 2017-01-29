<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsSellersCommissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_sellers_commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_id')->unsigned();
            $table->integer('seller_id')->unsigned();
            $table->integer('commission_type_id')->unsigned();
            $table->decimal('percentage_applied');
            $table->decimal('extra_percentage_applied');
            $table->decimal('value');
            $table->timestamps();

            $table->unique(['sale_id', 'seller_id']);

            $table->foreign('sale_id')->references('id')->on('sls_sales');
            $table->foreign('seller_id')->references('id')->on('sls_sellers');
            $table->foreign('commission_type_id')->references('id')->on('sls_commission_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_sellers_commissions');
    }
}
