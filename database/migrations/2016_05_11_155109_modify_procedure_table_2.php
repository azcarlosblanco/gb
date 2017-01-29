<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProcedureTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procedure_entry', function ($table) {
            $table->integer('responsible')->unsigned()->nullable()->default(NULL)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('procedure_entry', function ($table) {
            $table->integer('responsible')->unsigned()->change();
        });
    }
}
