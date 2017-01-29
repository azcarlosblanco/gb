<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberMemberPlanTypeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_type', function(Blueprint $table)
        {
            $table->tinyInteger("num_members");
        });

        \DB::table("plan_type")
                ->where("name","Individual")
                ->update(["num_members"=>1]);

        \DB::table("plan_type")
                ->where("name","2 Members")
                ->update(["num_members"=>2]);

        \DB::table("plan_type")
                ->where("name","Familiar")
                ->update(["num_members"=>3]);
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

        });
    }

}
