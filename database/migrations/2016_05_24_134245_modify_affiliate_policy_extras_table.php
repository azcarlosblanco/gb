<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyAffiliatePolicyExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_policy_extras', function ($table) {
                $table->string('causas');
                //1 amendments / 2 exclusions
                $table->smallInteger('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_policy_extras', function ($table) {
                $table->dropColumn('causas');
                $table->string('causas',10)->change();
        });
    }
}
