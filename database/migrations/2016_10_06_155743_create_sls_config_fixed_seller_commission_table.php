<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsConfigFixedSellerCommissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_config_fixed_seller_commission', function (Blueprint $table) {
           $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('seller_id')->unsigned();
            $table->decimal('percentage');
            $table->timestamps();

            $table->unique(['company_id', 'seller_id']);

            $table->foreign('company_id')->references('id')->on('sls_companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('seller_id')->references('id')->on('sls_sellers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_config_fixed_seller_commission');
    }
}
