<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;

class AddInsTypePlanTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('plan', function(Blueprint $table)
        {
            $table->integer('insurance_type_id')->unsigned();
            $table->foreign('insurance_type_id')
                  ->references('id')->on('insurance_type')
                  ->onDelete('Cascade');
        });

        $exist = \DB::table('insurance_type')
                        ->where('name','health')
                        ->first();
        if($exist==null){
            $id = \DB::table('insurance_type')->insertGetId(
                        ['name' => 'health', 'display_name' => "Salud"]
                    );
            \DB::table('plan')->update(
                                    ['insurance_type_id'=>$id]
                                );

        }
        
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
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
