<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFileEntryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_entry', function ($table) {
            $table->string('driver');
            $table->string('complete_path');
            $table->dropColumn('alternative_path');
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
            $table->dropColumn('driver');
            $table->dropColumn('complete_path');
            $table->string('alternative_path');
        });
    }
}
