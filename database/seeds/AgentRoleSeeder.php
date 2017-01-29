<?php

use Illuminate\Database\Seeder;

class AgentRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role')->insert(
        array(
            'name' => 'agente',
            'display_name' => 'Usuario Agente',
            'description' => 'Usuario Agente',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));
    }
}
