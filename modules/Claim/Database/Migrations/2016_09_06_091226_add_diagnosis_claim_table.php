<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiagnosisClaimTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim', function(Blueprint $table)
        {
            $table->integer('diagnosis_id')->unsigned()->nullable();
            $table->foreign('diagnosis_id')
                    ->references('id')->on('diagnosis')
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
        Schema::table('claim', function(Blueprint $table)
        {
            $table->dropForeign('claim_diagnosis_id_foreign');
            $table->dropColumn('diagnosis_id');
        });
    }

}
