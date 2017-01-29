<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionButtonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_button', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('icon');
                $table->string('link');
                $table->integer('process_catalog_id')->unsigned();
                $table->timestamps();
                $table->foreign('process_catalog_id')
                    ->references('id')->on('process_catalog')
                    ->onDelete('Cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('action_button');
    }
}
