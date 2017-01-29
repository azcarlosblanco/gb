<?php

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
      factory(Modules\Customer\Entities\Customer::class, 10)->create();
    }
}
