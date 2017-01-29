<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('procedure_document', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('name', 20);
        $table->string('description')->nullable();
        $table->string('type', 10)->default('other');
        $table->integer('procedure_catalog_id')->unsigned();

        $table->foreign('procedure_catalog_id')
                    ->references('id')->on('procedure_catalog')
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
        Schema::drop('procedure_document');
    }
}
