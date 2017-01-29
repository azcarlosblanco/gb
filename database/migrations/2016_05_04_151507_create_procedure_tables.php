<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('procedure_catalog', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procedure_entry', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable()->default(null);
            $table->string('state');
            $table->integer('policy_id')->unsigned();
            $table->integer('procedure_catalog_id')->unsigned();
            $table->integer('responsible')->unsigned();
            $table->foreign('policy_id')
                        ->references('id')
                        ->on('policy')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('procedure_catalog_id')
                        ->references('id')
                        ->on('procedure_catalog')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('responsible')
                        ->references('id')
                        ->on('user')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
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
        Schema::drop('procedure_entry');
        Schema::drop('procedure_catalog');
    }
}
