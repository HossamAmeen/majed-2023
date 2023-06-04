<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */


use Faker\Generator as Faker;

$factory->define(App\Models\BillsProduct::class, function (Faker $faker) {
    return [
        'product_price' => rand(100,500),
        'selling_price' => rand(200 , 600),
        'discount'      => rand(20,80),
        'quantity'      => rand(1,8),  
        'product_id'=>App\Models\Product::all()->random()->id,
        'bill_id'=>App\Models\Bill::all()->random()->id,
    ];
});
