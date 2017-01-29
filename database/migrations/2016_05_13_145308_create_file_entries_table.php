<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_entry', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('filename');
                $table->string('mime');
                $table->string('original_filename');
                $table->string('table_type');
                $table->integer('table_id')->unsigned();
                $table->string('description');
                $table->string('alternative_path');
                $table->timestamps();
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
        Schema::drop('file_entry');
    }

}
