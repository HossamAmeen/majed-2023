<?php

use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        $this->addCity();
        \App\Models\User::create([
            'full_name'=> 'admin',
            'email' => 'asd@asd.asd',
            'password' => bcrypt('admin'),
            'user_name' => "admin",
            'phone' => "01010079798",
            'user_id' => "1",
            'role' => 1,
        ]);
    }
    public function addCity()
    {
        \App\Models\Governorate::create([
            'name'=> 'القاهره',
            'en_name'=> 'Cairo',
           
        ]);

        \App\Models\Governorate::create([
            'name'=> 'اسيوط',
            'en_name'=> 'Assiut',
           
        ]);
        \App\Models\City::create([
            'name'=> 'القاهره',
            'en_name'=> 'Cairo',
            'governorate_id' =>1
           
        ]);
        \App\Models\City::create([
            'name'=> 'اسيوط',
            'en_name'=> 'Assiut',
            'governorate_id'=> 2
           
        ]);
       
    }

}
