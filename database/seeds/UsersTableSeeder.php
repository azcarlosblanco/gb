<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ////Llenar de datos de usuario por default en el sistema
        //DB::table('users')->truncate();
        //DB::table('role_user')->truncate();
        //DB::table('permissions')->truncate();
        //DB::table('permission_role')->truncate();

        $user = new User();
        $user->name = "admin";
        $user->lastname = "lastname";
        $user->email = "admin@example.com";
        $user->password = Hash::make('123456');
        $user->created_at = Carbon\Carbon::now();
        $user->updated_at = Carbon\Carbon::now();
        $user->save();

        $admin_id=\DB::table('role')
                        ->where('name','administracion')
                        ->select('id')
                        ->first()
                        ->id;

        DB::table('role_user')->insert(
        array(
            'user_id' => $user->id,
            'role_id' => $admin_id,
        ));

        //creacion de los permisimos para el module user
        DB::table('permission')->insert(
        array(
            'id' => 11,
            'name' => 'user_access',
            'display_name' => 'user_access',
        ));

        DB::table('permission')->insert(
        array(
            'id' => 12,
            'name' => 'user_create',
            'display_name' => 'user_create',
        ));

        DB::table('permission')->insert(
        array(
            'id' => 13,
            'name' => 'user_edit',
            'display_name' => 'user_edit',
        ));

        DB::table('permission')->insert(
        array(
            'id' => 14,
            'name' => 'user_delete',
            'display_name' => 'user_delete',
        ));

        //asignacion de permisos del module a los roles
        //administracion = 1
        //emision = 2
        //recepcion = 3
        //
        //administracion todos
        DB::table('permission_role')->insert(
        array(
            'role_id' => 1,
            'permission_id' => 11 ,
        ));

        DB::table('permission_role')->insert(
        array(
            'role_id' => 1,
            'permission_id' => 12 ,
        ));

        DB::table('permission_role')->insert(
        array(
            'role_id' => 1,
            'permission_id' => 13 ,
        ));

        DB::table('permission_role')->insert(
        array(
            'role_id' => 1,
            'permission_id' => 14 ,
        ));

        //emision: edit and access
        DB::table('permission_role')->insert(
        array(
            'role_id' => 2,
            'permission_id' => 11 ,
        ));

        DB::table('permission_role')->insert(
        array(
            'role_id' => 2,
            'permission_id' => 13 ,
        ));

        //recepcion: edit and access
        DB::table('permission_role')->insert(
        array(
            'role_id' => 3,
            'permission_id' => 11 ,
        ));

        DB::table('permission_role')->insert(
        array(
            'role_id' => 3,
            'permission_id' => 13 ,
        ));
    }
}
