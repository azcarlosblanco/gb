<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableClaimConcept extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_concept', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name');
            $table->boolean('notify')->default(0);
            $table->softDeletes();
        });

        Schema::table('claim_file', function(Blueprint $table)
        {
          $table->integer('concept')->unsigned()->nullable()->change();
          $table->foreign('concept')
                  ->references('id')->on('claim_concept')
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
        Schema::table('claim_file', function(Blueprint $table)
        {
          $table->dropForeign('claim_file_concept_foreign');
          $table->string('concept')->nullable()->change();
        });

        Schema::drop('claim_concept');
    }

}
