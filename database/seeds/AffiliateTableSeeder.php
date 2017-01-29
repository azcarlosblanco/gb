<?php

use Illuminate\Database\Seeder;

class AffiliateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
      factory(Modules\Affiliate\Entities\Affiliate::class, 10)->create();
    }
}
