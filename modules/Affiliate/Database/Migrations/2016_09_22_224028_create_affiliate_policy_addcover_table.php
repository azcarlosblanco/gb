<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliatePolicyAddcoverTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_policy_addcoverv', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date('effective_date');
            $table->integer('pd_acv_id')->unsigned();
            $table->integer('affiliate_policy_id')->unsigned();
            $table->timestamps();
            $table->foreign('pd_acv_id')
                    ->references('id')->on('plan_deducible_addcover_value')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->foreign('affiliate_policy_id')
                    ->references('id')->on('affiliate_policy')
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
        Schema::drop('affiliate_policy_addcover');
    }

}
