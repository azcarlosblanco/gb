<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
      Schema::create('affiliate_role', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique();
          $table->softDeletes();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
      Schema::drop('affiliate_role');
    }
}
