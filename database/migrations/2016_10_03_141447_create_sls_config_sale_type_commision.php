<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsConfigSaleTypeCommision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_config_sale_type_commission', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('sale_type_id')->unsigned();
            $table->decimal('percentage');
            $table->timestamps();

            $table->unique(['company_id', 'sale_type_id']);
            
            $table->foreign('company_id')->references('id')->on('sls_companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_config_sale_type_commission');
    }
}
