<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(Modules\Affiliate\Entities\Affiliate::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'lastname' => $faker->lastname,
        'sex'=> $faker->randomElement(array('m', 'f')),
        'dob' => $faker->date(),
        'height' => $faker->randomNumber(3),
        'weight' => $faker->randomNumber(3),
        'pid_type' => $faker->numberBetween(1, 3),
        'pid_num' => $faker->randomNumber(9)
    ];
});

$factory->define(Modules\Customer\Entities\Customer::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'lastname' => $faker->lastName,
        'pid_type' => $faker->numberBetween(1, 3),
        'pid_num' => $faker->randomNumber(9),
        'address' => $faker->address(),
        'phone' => $faker->randomNumber(9),
        'mobile' => $faker->randomNumber(9),
        'fax' => $faker->randomNumber(9),
        'email' => $faker->email,
        'country_id' => 1,
        'state_id' => 1,
        'city_id' => 1
    ];
});
