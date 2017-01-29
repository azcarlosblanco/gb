<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsCompaniesCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_companies_commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_id')->unsigned()->unique();
            $table->integer('commission_type_id')->unsigned();
            $table->decimal('percentage_applied');
            $table->decimal('value');
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sls_sales');
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
        Schema::drop('sls_companies_commissions');
    }
}
