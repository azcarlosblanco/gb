<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy', function ($table) {
                $table->date('endoso_date')->nullable()->change();
                $table->integer('parent_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('policy', function ($table) {
                $table->date('endoso_date')->change();
                $table->integer('parent_id')->unsigned()->change();
        });
    }
}
