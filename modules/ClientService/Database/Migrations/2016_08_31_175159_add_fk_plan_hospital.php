<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkPlanHospital extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_hospital', function(Blueprint $table)
        {
           $table->integer("hospital_id")->unsigned();
            $table->foreign('hospital_id')
                    ->references('id')->on('hospital')
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
