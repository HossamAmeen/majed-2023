<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Bill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Generator as Faker;
use App\Models\User;
$factory->define(App\Models\Bill::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'phone' => $faker->phoneNumber,
        'user_id'=>App\Models\User::all()->random()->id,
       

    ];
});
