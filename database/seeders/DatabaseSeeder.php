<?php

namespace Database\Seeders;

use App\Models\Configration;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeed::class,
            // CategorySeeder::class,
            // ProductSeeder::class,
            // OrderSeeder::class,
            // BillSeeder::class,
        ]);

        Configration::factory()->create();
    }
}
