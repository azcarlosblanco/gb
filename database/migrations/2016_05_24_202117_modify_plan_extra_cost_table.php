<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPlanExtraCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('plan_extra_cost');

        Schema::create('plan_extra_cost', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('reason');
            $table->integer('plan_deducible_id')->unsigned();
            $table->integer('number_payments_id')->unsigned();
            $table->string('reason_value');
            $table->float('value');
            $table->foreign('plan_deducible_id')
                    ->references('id')->on('plan_deducible')
                    ->onDelete('Cascade');
            $table->foreign('number_payments_id')
                    ->references('id')->on('number_payments')
                    ->onDelete('Cascade');
            $table->unique(
                        ['plan_deducible_id',
                         'number_payments_id',
                         'reason',
                         'reason_value'], 'values_payment_ukey');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('plan_extra_cost', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('reason');
            $table->integer('plan_deducible_id')->unsigned();
            $table->integer('number_payments_id')->unsigned();
            $table->string('reason_value');
            $table->float('value');
            $table->foreign('plan_deducible_id')
                    ->references('id')->on('plan_deducible')
                    ->onDelete('Cascade');
            $table->foreign('number_payments_id')
                    ->references('id')->on('number_payments')
                    ->onDelete('Cascade');
            $table->unique(
                        ['plan_deducible_id',
                         'number_payments_id',
                         'reason',
                         'reason_value'], 'values_payment_ukey');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
