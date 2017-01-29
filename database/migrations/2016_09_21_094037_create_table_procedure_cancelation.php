<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProcedureCancelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('procedure_cancellation', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reason');
            $table->integer('responsible_id')->unsigned();
            $table->integer('procedure_entry_id')->unsigned();
            $table->softDeletes();
            $table->foreign('responsible_id')
                    ->references('id')->on('user')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->foreign('procedure_entry_id')
                    ->references('id')->on('procedure_entry')
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
        Schema::drop('procedure_cancellation');
    }
}
