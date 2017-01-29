<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestPolicyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_policy_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->text('data');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('process_id')
                    ->references('id')->on('process_entry')
                    ->onDelete('Cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_policy_data');
    }
}
