<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAffiliatePid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate', function (Blueprint $table) {
          $table->tinyInteger('pid_type')->unsigned();
          $table->string('pid_num');
          $table->unique(['pid_type', 'pid_num']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate', function (Blueprint $table) {
          $table->dropUnique(['pid_type', 'pid_num']);
          $table->dropColumn(['pid_type', 'pid_num']);
        });
    }
}
