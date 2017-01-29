<?php

use Illuminate\Database\Seeder;

class AffiliateRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
      DB::table('affiliate_role')->insert([
        array('name' => 'titular'),
        array('name' => 'esposo(a)'),
        array('name' => 'dependiente')
      ]);
    }
}
