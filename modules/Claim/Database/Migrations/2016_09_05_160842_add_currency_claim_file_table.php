<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyClaimFileTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_file', function(Blueprint $table)
        {
          $table->integer('currency_id')->unsigned()->nullable();
          $table->foreign('currency_id')
                  ->references('id')->on('currency')
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
        Schema::table('claim_file', function(Blueprint $table)
        {
            $table->dropForeign('claim_file_currency_id_foreign');
            $table->dropColumn('currency_id');
        });
    }

}
