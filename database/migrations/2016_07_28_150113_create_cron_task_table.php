<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCronTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('cron_task', function(Blueprint $table)
          {
              $table->increments('id');
              //file_upload=>1
              $table->tinyInteger('type');
              $table->string('data');
              $table->string('action')->nullable();
              //0=>pending, 1=>success, 2=>finish with error, 3=>expired
              $table->tinyInteger('status')->default(0);
              $table->softDeletes();
              $table->timestamps();
              $table->dateTime('date_expire')->nullable();
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cron_task');
    }
}
