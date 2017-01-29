<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('description');
            $table->integer('insurance_company_id')->unsigned();
            $table->integer('parent_id')->unsigned()->default(0);
            $table->softDeletes();

            $table->unique(['code', 'insurance_company_id']);
            $table->foreign('insurance_company_id')
                        ->references('id')->on('insurance_company')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::create('quiz_item', function (Blueprint $table) {
          $table->increments('id');
          $table->string('description');
          $table->enum('resp_type', ['bool', 'text']);
          $table->integer('quiz_id')->unsigned();
          $table->softDeletes();

          $table->foreign('quiz_id')
                      ->references('id')->on('quiz')
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
        Schema::drop('quiz_item');
        Schema::drop('quiz');
    }
}
