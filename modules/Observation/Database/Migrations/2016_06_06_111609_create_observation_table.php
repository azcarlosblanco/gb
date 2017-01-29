<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObservationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observation', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('item_id')->unsigned()->default(0);
            $table->string('item_ref')->nullable();
            $table->text('content');
            $table->integer('type_id')->unsigned();//(0=>subsanable user, 1=>subsanable client, 2=>no subsanable)
            $table->tinyInteger('status')->default(0);//(0=>pending, 1=>solved)

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('process_id')
                        ->references('id')->on('process_entry')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');

            $table->foreign('type_id')
                        ->references('id')->on('observation_type')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('observation');
    }

}
