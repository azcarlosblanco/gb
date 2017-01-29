<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMethodPaymenteDetailsTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_brand', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string("display_name");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('credit_card_way_pay', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string("display_name");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('credit_card_type', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string("display_name");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('credit_card_payment_detail', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date("payment_date");
            $table->float("value");
            $table->integer("credit_card_type_id")->unsigned();
            $table->integer("credit_card_brand_id")->unsigned();
            $table->integer("credit_card_way_pay_id")->unsigned();
            $table->integer("policy_cost_id")->unsigned();
            $table->string("card_num"); //just the first 4 digits
            $table->string("expire_date"); //no guardar hasta saber las normas
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('credit_card_type_id')
                        ->references('id')->on('credit_card_type')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('credit_card_brand_id')
                        ->references('id')->on('credit_card_brand')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('credit_card_way_pay_id')
                        ->references('id')
                        ->on('credit_card_way_pay')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
            $table->foreign('policy_cost_id')
                        ->references('id')
                        ->on('policy_cost')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::create('bank_account_type', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string("display_name");
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('transfer_payment_detail', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date("payment_date");
            $table->float("value");
            $table->integer("transfer_num")->unsigned();
            $table->integer("bank_name")->unsigned();
            $table->integer("bank_account_type_id")->unsigned();
            $table->integer("policy_cost_id")->unsigned();
            $table->string("titular_account");
            $table->string("account_num_from");
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('policy_cost_id')
                        ->references('id')
                        ->on('policy_cost')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::create('cheque_payment_detail', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date("payment_date");
            $table->float("value");
            $table->integer("cheque_num")->unsigned();
            $table->string("bank_name");
            $table->integer("policy_cost_id")->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('policy_cost_id')
                        ->references('id')
                        ->on('policy_cost')
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
        });

        Schema::create('deposit_payment_detail', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date("payment_date");
            $table->float("value");
            $table->integer("desposit_num")->unsigned();
            $table->integer("bank_name")->unsigned();
            $table->integer("account_num")->unsigned();
            $table->integer("policy_cost_id")->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('policy_cost_id')
                        ->references('id')
                        ->on('policy_cost')
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
        Schema::drop('cheque_payment_detail');
        Schema::drop('transfer_payment_detail');
        Schema::drop('bank_account_type');
        Schema::drop('credit_card_payment_detail');
        Schema::drop('deposit_payment_detail');
        Schema::drop('credit_card_type');
        Schema::drop('credit_card_way_pay');
        Schema::drop('credit_card_brand');
    }

}
