<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ////create role administrador
        //DB::table('roles')->truncate();

        DB::table('role')->insert(
        array(
            'id' => 1,
            'name' => 'administracion',
            'display_name' => 'User Administrator',
            'description' => 'User is allowed to manage all the system',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));

        DB::table('role')->insert(
        array(
            'id' => 2,
            'name' => 'emision',
            'display_name' => 'User Emisiones',
            'description' => 'User is allowed to manage emissions',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));

        DB::table('role')->insert(
        array(
            'id' => 3,
            'name' => 'recepcion',
            'display_name' => 'User Recepciones',
            'description' => 'User is allowed to manage receptions',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));

        DB::table('role')->insert(
        array(
            'name' => 'emision_manager',
            'display_name' => 'Maneja Emisiones',
            'description' => 'Usuario maneja Emisiones',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));

        DB::table('role')->insert(
        array(
            'name' => 'recepcion_manager',
            'display_name' => 'Maneja Recepciones',
            'description' => 'Usuario Maneja Recepciones',
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ));
    }
}
