<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProcessCatalalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('process_catalog', function ($table) {
            $table->string('icon', 60);
            $table->string('link', 250);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_catalog', function ($table) {
            $table->dropColumn('icon');
            $table->dropColumn('link');
        });
    }
}
