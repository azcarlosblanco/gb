<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('lastname');
            $table->integer('user_id')->unsigned();
            $table->string('document_id')->unique();
            $table->foreign('user_id')->references('id')->on('user')
                ->onUpdate('cascade')->onDelete('cascade');
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
        //
    }
}
