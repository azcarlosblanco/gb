<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsConfigServiceTypeCommissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_config_service_type_commission', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('service_type_id')->unsigned();
            $table->decimal('percentage');
            $table->timestamps();

            $table->unique(['company_id', 'service_type_id'], 'company_service_type_ids');
            
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
        Schema::drop('sls_config_service_type_commission');
    }
}
