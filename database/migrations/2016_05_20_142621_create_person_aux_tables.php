<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonAuxTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
      Schema::create('person_sex', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique();
          $table->softDeletes();
      });

      Schema::create('person_status', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique();
          $table->softDeletes();
      });

      Schema::create('person_doctype', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique();
          $table->softDeletes();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
      Schema::drop('person_sex');
      Schema::drop('person_status');
      Schema::drop('person_doctype');
    }
}
