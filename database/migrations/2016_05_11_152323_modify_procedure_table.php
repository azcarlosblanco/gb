<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProcedureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procedure_entry', function ($table) {
            $table->integer('policy_id')->unsigned()->nullable()->default(NULL)->change();
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
            $table->integer('policy_id')->unsigned()->change();
        });
    }
}
