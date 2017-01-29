<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_catalog', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('department')->unsigned();
            $table->integer('procedure_catalog_id')->unsigned();
            $table->integer('next_process')->unsigned()->nullable()->default(null);
            $table->integer('group');
            $table->integer('seq_number');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('department')
                        ->references('id')->on('role')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('procedure_catalog_id')
                        ->references('id')->on('procedure_catalog')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('next_process')
                        ->references('id')->on('process_catalog')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::create('process_entry', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_catalog_id')->unsigned();
            $table->integer('procedure_entry_id')->unsigned();
            $table->integer('responsible')->unsigned()->nullable()->default(null);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable()->default(null);
            $table->string('state');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('process_catalog_id')
                        ->references('id')->on('process_catalog')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('procedure_entry_id')
                        ->references('id')->on('procedure_entry')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('responsible')
                        ->references('id')
                        ->on('user')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('process_entry');
        Schema::drop('process_catalog');        
    }
}
