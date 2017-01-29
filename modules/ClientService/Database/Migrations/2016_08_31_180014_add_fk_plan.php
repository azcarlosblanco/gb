<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkPlan extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_hospital', function(Blueprint $table)
        {
            $table->integer("plan_id")->unsigned();
            $table->foreign('plan_id')
                    ->references('id')->on('plan')
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
        Schema::table('plan_hospital', function(Blueprint $table)
        {

        });
    }

}
