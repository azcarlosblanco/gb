<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployeeDepartment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for associating roles to users (Many-to-Many)
        Schema::create('employee_department', function (Blueprint $table) {
            $table->integer('employee_id')->unsigned();
            $table->integer('department_id')->unsigned();
            $table->integer('state'); //1 active, 0 inactive

            $table->foreign('employee_id')
                        ->references('id')
                        ->on('employee')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
            $table->foreign('department_id')
                        ->references('id')
                        ->on('department')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
            $table->primary(['employee_id', 'department_id']);
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
