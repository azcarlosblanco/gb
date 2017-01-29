<?php

use Illuminate\Database\Seeder;

class CountryStateCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
      DB::table('country')->insert([
            'name' => 'Ecuador',
            'iso2' => 'EC',
            'iso3' => 'ECU',
            'code' => '218'
      ]);

      DB::table('state')->insert([
            'name' => 'Guayas',
            'country_id' => 1
      ]);

      DB::table('state')->insert([
            'name' => 'Pichincha',
            'country_id' => 1
      ]);

      DB::table('city')->insert([
            'name' => 'Guayaquil',
            'state_id' => 1
      ]);

      DB::table('city')->insert([
            'name' => 'Quito',
            'state_id' => 2
      ]);
    }
}
