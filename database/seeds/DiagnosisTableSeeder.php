<?php

use Illuminate\Database\Seeder;

class DiagnosisTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('diagnosis')->insert([
            'name' => 'diagnosis_1',
            'display_name' => 'Diagnostico Prueba 1'
      ]);
      DB::table('diagnosis')->insert([
            'name' => 'diagnosis_2',
            'display_name' => 'Diagnostico Prueba 2'
      ]);
      DB::table('diagnosis')->insert([
            'name' => 'diagnosis_3',
            'display_name' => 'Diagnostico Prueba 3'
      ]);
    }
}
