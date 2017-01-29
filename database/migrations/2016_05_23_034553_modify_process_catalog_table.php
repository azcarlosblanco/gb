<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProcessCatalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('process_catalog', function ($table) {
            $table->dropForeign('process_catalog_next_process_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_catalog', function ($table) {
            $table->foreign('next_process')
                    ->references('id')->on('process_catalog')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
        });
    }
}
