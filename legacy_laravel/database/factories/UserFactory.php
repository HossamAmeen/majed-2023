<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_name' => $this->faker->name(),
            'full_name' => $this->faker->name(),
            // "admin"
            'password' => '$2y$10$mXwEFI/nQub9PmCejn59zuozRujElm4bu5D01y.wXpciRnKjHRWNm',
            'email' => Str::random(10).$this->faker->email(),
            'role' => $this->faker->randomElement(range(0, 1)),
        ];
    }
}
