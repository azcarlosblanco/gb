<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRoleAffiliatePolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_policy', function (Blueprint $table) {
          $table->integer('role')->unsigned()->change();
          $table->foreign('role')
                  ->references('id')->on('affiliate_role')
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
        Schema::table('affiliate_policy', function (Blueprint $table) {
            //
        });
    }
}
