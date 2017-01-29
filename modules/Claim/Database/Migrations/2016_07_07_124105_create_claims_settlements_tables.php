<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimsSettlementsTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('claim', function(Blueprint $table)
      {
          $table->increments('id');
          $table->integer('affiliate_policy_id')->unsigned();
          $table->tinyInteger('status')->unsigned();
          $table->softDeletes();
          $table->timestamps();

          $table->foreign('affiliate_policy_id')
                  ->references('id')->on('affiliate_policy')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
      });

      Schema::create('claim_procedure', function(Blueprint $table)
      {
          $table->increments('id');
          $table->integer('claim_id')->unsigned();
          $table->integer('procedure_entry_id')->unsigned();

          $table->foreign('claim_id')
                  ->references('id')->on('claim')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->foreign('procedure_entry_id')
                  ->references('id')->on('procedure_entry')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->softDeletes();
          $table->timestamps();
      });

      Schema::create('claim_file', function(Blueprint $table)
      {
          $table->increments('id');
          $table->string('description');
          $table->integer('file_entry_id')->unsigned();
          $table->integer('claim_id')->unsigned();
          $table->integer('procedure_document_id')->unsigned();
          $table->integer('supplier_id')->unsigned()->nullable();
          $table->boolean('usa')->default(0);

          $table->date('date_invoice')->nullable();
          $table->float('amount')->unsigned()->default(0);
          $table->string('concept')->nullable();

          $table->foreign('claim_id')
                  ->references('id')->on('claim')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->foreign('file_entry_id')
                  ->references('id')->on('file_entry')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->foreign('procedure_document_id')
                  ->references('id')->on('procedure_document')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->softDeletes();
          $table->timestamps();
      });

      Schema::create('claim_settlement', function(Blueprint $table)
      {
          $table->increments('id');
          $table->integer('claim_file_id')->unsigned();
          $table->float('uncovered_value');
          $table->float('descuento');
          //que valor de la factura va para cubrir el deducible
          $table->float('deducible');
          $table->float('coaseguro');
          $table->float('refunded');
          $table->string('notes');
          $table->date('serv_date');
          $table->tinyInteger('status')->default(0);

          $table->foreign('claim_file_id')
                  ->references('id')->on('claim_file')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->softDeletes();
          $table->timestamps();
      });

      Schema::create('claim_settlement_refund', function(Blueprint $table)
      {
          $table->increments('id');
          $table->float('value');
          $table->integer('payment_method_id')->unsigned();
          $table->integer('claim_settlement_id')->unsigned();
          $table->boolean('to_supplier');

          $table->foreign('claim_settlement_id')
                  ->references('id')->on('claim_settlement')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

          $table->foreign('payment_method_id')
                  ->references('id')->on('payment_method')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

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
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('claim_file');
        Schema::dropIfExists('claim_file_affiliate');
        Schema::dropIfExists('claim_procedure');
        Schema::dropIfExists('claim');
        Schema::dropIfExists('claim_settlement');
        Schema::dropIfExists('claim_settlement_refund');
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
