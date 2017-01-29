<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileUploadEntryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('file_upload_entry', function(Blueprint $table)
          {
              $table->increments('id');
              $table->tinyInteger('expected');
              $table->tinyInteger('completed')->default(0);
              $table->string('table_type');
              $table->integer('table_id')->unsigned();
              $table->text('data');
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
        Schema::drop('file_upload_entry');
    }
}
