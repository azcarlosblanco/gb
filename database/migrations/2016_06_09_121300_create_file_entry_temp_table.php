<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileEntryTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_entry_temp', function(Blueprint $table)
        {
          $table->increments('id');
          $table->string('filename');
          $table->string('mime');
          $table->string('original_filename');
          $table->string('description');
          $table->string('driver');
          $table->string('complete_path');

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
        Schema::drop('file_entry_temp');
    }
}
