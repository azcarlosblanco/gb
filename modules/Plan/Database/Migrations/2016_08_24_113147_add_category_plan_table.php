<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryPlanTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan', function(Blueprint $table)
        {
          $table->integer('plan_category_id')->unsigned()->nullable();
          $table->foreign('plan_category_id')
                  ->references('id')->on('plan_category')
                  ->onDelete('Cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function(Blueprint $table)
        {
          \DB::statement('SET FOREIGN_KEY_CHECKS=0');
          $table->dropColumn('plan_category_id');
          \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }

}
