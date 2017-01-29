<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date("date_quotation");
            $table->integer("agent_id")->unsigned();
            $table->string("client_name");
            $table->string("client_email");
            $table->string("client_phone");
            $table->text('obj_quotation');
            $table->foreign('agent_id')
                    ->references('id')->on('agente')
                    ->onUpdate('cascade')
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
        Schema::drop('quotation');
    }

}
