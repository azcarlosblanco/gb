<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFilesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('file_entry', function(Blueprint $table)
      {
          $table->text('data')->nullable()->default('');
      });

      Schema::table('file_entry_temp', function(Blueprint $table)
      {
          $table->text('data')->nullable()->default('');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('file_entry', function ($table) {
        $table->dropColumn('data');
      });

      Schema::table('file_entry_temp', function ($table) {
        $table->dropColumn('data');
      });
    }
}
