<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Category;
use Faker\Generator as Faker;
use App\Models\User;
$factory->define(App\Models\Category::class, function (Faker $faker) {
    return [


        'name' => $faker->name,
        'user_id'=>User::all()->random()->id,

        ];

});

