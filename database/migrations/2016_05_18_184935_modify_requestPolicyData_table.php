<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyRequestPolicyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_policy_data', function ($table) {
            $table->string('customer_identity', 60);
            $table->string('customer_fullname', 250);
            $table->text('data')->change();
            $table->integer('plan_id')->unsigned();
            $table->integer('agente_id')->unsigned();
            $table->foreign('plan_id')
                        ->references('id')->on('plan')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('agente_id')
                        ->references('id')->on('agente')
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
        Schema::table('request_policy_data', function ($table) {
            $table->dropColumn('customer_identity');
            $table->dropColumn('customer_fullname');
            $table->dropColumn('plan_id');
        });
    }
}
