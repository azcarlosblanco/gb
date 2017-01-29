<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsTablePolicy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy', function (Blueprint $table) {
          $table->string('policy_number');
          $table->integer('plan_deducible_id')->unsigned();
          $table->integer('agente_id')->unsigned();
          $table->integer('payments_number_id')->unsigned();
          $table->integer('plan_type_id')->unsigned();
          $table->integer('emision_number')->unsigned();
          $table->integer('endoso_number')->unsigned();
          $table->integer('renewal_number')->unsigned();
          $table->date('start_date');
          $table->date('end_date');
          $table->date('endoso_date');
          $table->date('emision_date');
          $table->integer('ptype')->unsigned();
          $table->integer('customer_id')->unsigned();
          $table->integer('parent_id')->unsigned();
          $table->foreign('plan_deducible_id')
                      ->references('id')->on('plan_deducible')
                      ->onUpdate('cascade')
                      ->onDelete('restrict');
          $table->foreign('agente_id')
                      ->references('id')->on('agente')
                      ->onUpdate('cascade')
                      ->onDelete('restrict');

          $table->foreign('payments_number_id')
                      ->references('id')->on('number_payments')
                      ->onUpdate('cascade')
                      ->onDelete('restrict');

          $table->foreign('plan_type_id')
                      ->references('id')->on('plan_type')
                      ->onUpdate('cascade')
                      ->onDelete('restrict');

          /*$table->foreign('customer_id')
                      ->references('id')->on('customer')
                      ->onUpdate('cascade')
                      ->onDelete('restrict');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policy', function (Blueprint $table) {
            //
        });
    }
}
