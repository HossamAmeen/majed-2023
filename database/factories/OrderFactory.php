<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\User;
$factory->define(App\Models\Order::class, function (Faker $faker) {
    $array=['sold-out','Returned'];
    return [
        'product_price'=>rand(150 , 550),
        'price'=>$faker->numberBetween(50, 500),
        'quantity'=> $faker->randomFloat(2, 1, 10),
        'date'=>$faker->date($format = 'Y-m-d', $max = 'now'),
        'status'=>$faker->randomElement($array),
        'product_name'=>$faker->name,
        'discount'=> $faker->randomDigit,
        'bill_id'=>App\Models\Bill::all()->random()->id,
        'user_id'=>App\Models\User::all()->random()->id,
        'product_id'=>App\Models\Product::all()->random()->id,
    ];
});
