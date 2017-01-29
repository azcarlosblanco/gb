<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgenteTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agente', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('lastname');
            $table->string('identity_document');
            $table->date('dob');
            $table->string('email');
            $table->string('skype');
            $table->string('mobile');
            $table->string('phone');
            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->string('address');
            //true or false, indicate if the agent is onder other agent
            $table->boolean('subagent');
            $table->smallInteger('comision');
            //if the agent is under other agent, this field hold the id of the agnet over him
            $table->integer('leader')->unsigned()->nullable()->default(NULL);
            $table->foreign('leader')
                    ->references('id')->on('agente')
                    ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('agente');
    }

}
