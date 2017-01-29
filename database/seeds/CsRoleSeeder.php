<?php

use Illuminate\Database\Seeder;

class CsRoleSeeder extends Seeder
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
            'name' => 'client_service',
            'display_name' => 'Usuario Servicio al Cliente',
            'description' => 'User is allowed to manage client service',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));

        DB::table('role')->insert(
        array(
            'name' => 'client_service_manager',
            'display_name' => 'Maneja Servicio al Cliente',
            'description' => 'Usuario maneja servicio al cliente',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));
    }
}
