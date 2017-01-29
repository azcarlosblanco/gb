<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("company_id")->unsigned()->nullable();
            $table->decimal("total_amount");
            $table->datetime("sale_date");
            $table->datetime("payment_date");
            $table->datetime("confirmation_date");
            $table->integer("quote_number");
            $table->integer("seller_id")->unsigned()->nullable();
            $table->integer("plan_id")->unsigned();
            $table->integer("service_type_id")->unsigned();
            $table->integer("sale_type_id")->unsigned();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('sls_companies')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('seller_id')->references('id')->on('sls_sellers')->onDelete('set null')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_sales');
    }
}
