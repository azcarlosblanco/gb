<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliatePolicyExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_policy_extras', function (Blueprint $table) {
            $table->increments('id');
            $table->char('type', 3); //1 exclusion,2 enmienda
            $table->string('description')->nullable();
            $table->integer('affiliate_policy_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::drop('affiliate_policy_extras');
    }
}
