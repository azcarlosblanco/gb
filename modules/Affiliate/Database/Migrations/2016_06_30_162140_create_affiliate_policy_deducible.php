<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliatePolicyDeducible extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_policy_deducible', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('plan_deducible_type_id')->unsigned();
            $table->integer('affiliate_policy_id')->unsigned();
            $table->float('amount')->unsigned();

            $table->foreign('plan_deducible_type_id')
                    ->references('id')->on('plan_deducible_type')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->foreign('affiliate_policy_id')
                    ->references('id')->on('affiliate_policy')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('affiliate_policy_deducible');
    }

}
