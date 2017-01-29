<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePlanDeducibleOptions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_deducible_options', function(Blueprint $table)
        {
            $table->dropColumn('reason');
            $table->integer('plan_deducible_type_id')->unsigned()->nullable();

            $table->foreign('plan_deducible_type_id')
                    ->references('id')->on('plan_deducible_type')
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
        Schema::table('plan_deducible_options', function(Blueprint $table)
        {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $table->string('reason')->default('local');
            $table->dropColumn('plan_deducible_type_id');
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }

}
