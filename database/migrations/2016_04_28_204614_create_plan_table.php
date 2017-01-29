<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->integer('insurance_company_id')->unsigned();
            $table->foreign('insurance_company_id')
                    ->references('id')->on('insurance_company')
                    ->onDelete('Cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_deducible', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->integer('plan_id')->unsigned();
            $table->foreign('plan_id')
                    ->references('id')->on('plan')
                    ->onDelete('Cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_deducible_options', function(Blueprint $table)
        {
            $table->increments('id');
            $table->enum('reason', ['local', 'international', 'in_usa', 'out_usa']);
            $table->float('value');
            $table->integer('plan_deducible_id')->unsigned();
            $table->foreign('plan_deducible_id')
                    ->references('id')->on('plan_deducible')
                    ->onDelete('Cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        //monthly, anual, semianual, quarterly
        Schema::create('number_payments', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('number');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        //individual, familiar, 2 members
        Schema::create('plan_type', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->softDeletes();
            $table->timestamps();
        });

        //esto sirve en especial en best doctors para calcular si ha habido
        //algun cambio en los valores del plan del que haya que notificar
        Schema::create('plan_range_age', function(Blueprint $table)
        {
            $table->increments('id');
            $table->tinyInteger('start');
            $table->tinyInteger('end');
            $table->integer('plan_id')->unsigned();
            $table->foreign('plan_id')
                    ->references('id')->on('plan')
                    ->onDelete('Cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_cost', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('plan_deducible_id')->unsigned();
            $table->integer('plan_type_id')->unsigned();
            $table->integer('number_payments_id')->unsigned();
            $table->integer('start_age');
            $table->integer('end_age');
            $table->float('value');
            $table->foreign('plan_deducible_id')
                    ->references('id')->on('plan_deducible')
                    ->onDelete('Cascade');
            $table->foreign('plan_type_id')
                    ->references('id')->on('plan_type')
                    ->onDelete('Cascade');
            $table->foreign('number_payments_id')
                    ->references('id')->on('number_payments')
                    ->onDelete('Cascade');
            $table->unique(
                        ['plan_deducible_id',
                         'plan_type_id',
                         'number_payments_id',
                         'start_age',
                         'end_age'], 'values_payment_ukey');
            $table->softDeletes();
            $table->timestamps();
        });

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
                         'reason'], 'values_payment_ukey');
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
        Schema::drop('plan_extra_cost');
        Schema::drop('plan_cost');
        Schema::drop('plan_range_age');
        Schema::drop('plan_type');
        Schema::drop('number_payments');
        Schema::drop('plan_deducible_options');
        Schema::drop('plan_deducible');
        Schema::drop('plan');
    }

}
