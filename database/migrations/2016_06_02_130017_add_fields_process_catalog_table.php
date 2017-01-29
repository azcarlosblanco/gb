<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsProcessCatalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('process_catalog', function ($table) {
            $table->dropIndex('process_catalog_next_process_foreign');
            $table->dropColumn('next_process');
            //1 compulsory, 0 optional
            $table->smallInteger('compulsory')->default(1);
            //0 false, 1 true 
            $table->smallInteger('last_process')->default(0);
        });

        Schema::create('process_prerequisite', function (Blueprint $table) {
            $table->integer('prs_cat_id');
            $table->integer('pre_prs_cat_id');
            //$table->foreign('prs_cat_id')
                        //->references('id')->on('process_catalog')
                        //->onUpdate('cascade')
                        //->onDelete('restrict');
            //$table->foreign('pre_prs_cat_id')
                        //->references('id')->on('process_catalog')
                        //->onUpdate('cascade')
                        //->onDelete('restrict');
            $table->primary(['prs_cat_id', 'pre_prs_cat_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_catalog', function ($table) {
            $table->dropColumn('compulsory');
            $table->dropColumn('last_process');
        });
    }
}
