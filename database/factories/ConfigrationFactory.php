<?php

namespace Database\Factories;

use App\Models\Configration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configration>
 */
class ConfigrationFactory extends Factory
{
    protected $model = Configration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->safeEmail(),
            'title' => 'majed  ',
            'facebook' => 'https://www.facebook.com/',
            'twitter' => 'https://twitter.com/',
            'instagram' => 'https://www.instagram.com/',
            'video' => 'https://www.youtube.com/embed/AnBHcM-tZsM',
            'video2' => 'https://www.youtube.com/embed/AnBHcM-tZsM',
            'youtube' => 'https://www.youtube.com',
            'user_id' => 1,
        ];
    }
}
