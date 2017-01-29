<?php

use Illuminate\Database\Seeder;

class PersonAuxTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

      DB::table('person_sex')->insert([
        array('id'=> 1, 'name' => 'masculino'),
        array('id'=> 2, 'name' => 'femenino')
      ]);

      DB::table('person_status')->insert([
        array('id'=> 1, 'name' => 'soltero(a)'),
        array('id'=> 2, 'name' => 'casado(a)'),
        array('id'=> 3, 'name' => 'divorciado(a)'),
        array('id'=> 4, 'name' => 'union libre')
      ]);

      DB::table('person_doctype')->insert([
        array('id'=> 1, 'name' => 'cedula'),
        array('id'=> 2, 'name' => 'ruc'),
        array('id'=> 3, 'name' => 'pasaporte')
      ]);

    }
}
