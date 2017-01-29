<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('display_name');
            $table->integer('is_parent'); //is parent
            $table->integer('level'); //1,2,3
            $table->integer('order');
            $table->string('link');
            $table->string('icon');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('module_code');
            $table->foreign('module_code')
                    ->references('code')
                    ->on('module')
                    ->onDelete('restrict');
            $table->foreign('parent_id')
                    ->references('id')
                    ->on('menu')
                    ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
