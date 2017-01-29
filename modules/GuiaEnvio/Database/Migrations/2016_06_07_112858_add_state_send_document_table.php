<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateSendDocumentTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('send_document', function ($table) 
        {
            $table->string('state',60)->default('bysend');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('send_document', function(Blueprint $table)
        {
            $table->dropColumn('state',60)->default('bysend');
        });
    }

}
