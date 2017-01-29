<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcessidSendDocumentTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('send_document', function ($table) 
        {
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')
                    ->references('id')->on('process_entry')
                    ->onDelete('Restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('send_document', function ($table) 
        {
            $table->dropColumn('process_id');
        });
    }

}
