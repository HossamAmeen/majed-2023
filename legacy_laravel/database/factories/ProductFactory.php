<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'purchasing_price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 10, 100),
            'total_price' => $this->faker->randomFloat(2, 10, 100),
            'quantity' => $this->faker->numberBetween(50, 500),
            'code' => $this->faker->unique()->numerify('#####'),
            'discount' => $this->faker->randomFloat(2, 0, 1),
            'user_id' => User::query()->inRandomOrder()->value('id'),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
        ];
    }
}
