<?php

use Illuminate\Database\Seeder;

class PlanDeducibleType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('plan_deducible_type')->insert([
        array('id'=> 1, 'name' => 'local'),
        array('id'=> 2, 'name' => 'usa')
      ]);
    }
}
