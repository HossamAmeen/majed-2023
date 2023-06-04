<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{


    public function run()
    {

     //   factory('App\Models\User',10)->create();
     $this->call([
        UserSeed::class,
        // CategorySeeder::class,
        // ProductSeeder::class,
        // OrderSeeder::class,
        // BillSeeder::class
    ]);
       factory('App\Models\Configration',1)->create();
    //    factory(App\Models\Category::class,100)->create();
    //    factory(App\Models\Product::class,100)->create();
    //    factory(App\Models\Bill::class,100)->create();
    //    factory(App\Models\Order::class,100)->create();
    //    factory(App\Models\BillsProduct::class,100)->create();



    }
}
