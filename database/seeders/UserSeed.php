<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addCity();

        User::create([
            'full_name' => 'admin',
            'email' => 'asd@asd.asd',
            'password' => Hash::make('admin'),
            'user_name' => 'admin',
            'phone' => '01010079798',
            'user_id' => '1',
            'role' => 1,
        ]);
    }

    public function addCity(): void
    {
        Governorate::create([
            'name' => 'القاهره',
            'en_name' => 'Cairo',
        ]);

        Governorate::create([
            'name' => 'اسيوط',
            'en_name' => 'Assiut',
        ]);

        City::create([
            'name' => 'القاهره',
            'en_name' => 'Cairo',
            'governorate_id' => 1,
        ]);

        City::create([
            'name' => 'اسيوط',
            'en_name' => 'Assiut',
            'governorate_id' => 2,
        ]);
    }
}
