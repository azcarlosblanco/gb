
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMenuActionRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_action_role', function (Blueprint $table) {
            $table->integer('menu_action_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->foreign('menu_action_id')
                    ->references('id')
                    ->on('menu_action')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->foreign('role_id')
                    ->references('id')
                    ->on('role')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->primary(['menu_action_id', 'role_id']);
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
