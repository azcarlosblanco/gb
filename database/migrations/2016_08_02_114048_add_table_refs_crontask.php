<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableRefsCrontask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('cron_task', function (Blueprint $table) {
        $table->string('table_type')->default('');
        $table->string('table_id')->default('');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('cron_task', function ($table) {
        $table->dropColumn('table_type');
        $table->dropColumn('table_id');
      });
    }
}
