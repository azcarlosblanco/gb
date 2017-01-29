<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendDocumentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_document', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('reason',60);
            $table->integer('sender')->unsigned();
            $table->integer('receiver_id')->unsigned();
            $table->string('receiver_type',60);
            $table->string('receiver_name',60);
            $table->string('receiver_address',60);
            $table->string('receiver_phone',60);
            $table->foreign('sender')
                    ->references('id')->on('user')
                    ->onDelete('Cascade');
            $table->timestamps();
        });

        Schema::create('send_document_item', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('description');
            $table->integer('num_copies');
            $table->integer('send_document_id')->unsigned();
            $table->foreign('send_document_id')
                    ->references('id')->on('send_document')
                    ->onDelete('Cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('send_document_item');
        Schema::drop('send_document');
    }

}
