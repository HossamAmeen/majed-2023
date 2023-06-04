<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Producty;
use Faker\Generator as Faker;
use App\Models\Category;
use App\Models\User;
$factory->define(App\Models\Product::class, function (Faker $faker) {
    return [

           'name' => $faker->name,
           'purchasing_price'=>$faker->randomFloat(2, 10, 100),
           'selling_price'=>$faker->randomFloat(2, 10, 100),
           'total_price'=>$faker->randomFloat(2, 10, 100),
            'quantity'=> $faker->numberBetween(50, 500),
            'code'=>$faker->unique()->numerify('#####'),
           'discount'=>$faker->randomFloat(2, 0, 1),
           'user_id'=>User::all()->random()->id,
          'category_id'=> Category::all()->random()->id,



           ];
});
