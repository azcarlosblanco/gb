<?php

use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('currency')->insert([
            'name' => 'usd',
            'display_name' => 'USD'
      ]);

      DB::table('currency')->insert([
            'name' => 'euro',
            'display_name' => 'Euro'
      ]);
    }
}
