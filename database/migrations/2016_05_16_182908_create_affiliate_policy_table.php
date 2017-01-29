<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliatePolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_policy', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned();
            $table->integer('policy_id')->unsigned();
            $table->date('effective_date');
            $table->date('dismiss_date')->nullable();
            $table->decimal('premium_amount', 15, 2);
            $table->string('deductibles');
            // 1 owner , 2 spouse, 3 dependent
            $table->tinyInteger('role');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('affiliate_id')
                        ->references('id')->on('affiliate')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');

            $table->foreign('policy_id')
                        ->references('id')->on('policy')
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
        Schema::drop('affiliate_policy');
    }
}
