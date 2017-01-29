<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlsSalesDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sls_sales_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("sale_id")->unsigned();
            $table->string("concept");
            $table->decimal("amount");
            $table->boolean("apply_commission");
            $table->timestamps();

            $table->foreign("sale_id")->references("id")->on("sls_sales")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sls_sales_details');
    }
}
