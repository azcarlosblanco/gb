<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_category', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('supplier', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('category')->unsigned();

            $table->foreign('category')
                    ->references('id')->on('supplier_category')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('supplier');
        Schema::drop('supplier_category');
    }

}
