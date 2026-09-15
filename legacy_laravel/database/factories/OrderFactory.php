<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_price' => $this->faker->numberBetween(150, 550),
            'price' => $this->faker->numberBetween(50, 500),
            'quantity' => $this->faker->randomFloat(2, 1, 10),
            'date' => $this->faker->date('Y-m-d'),
            'status' => $this->faker->randomElement(['sold-out', 'Returned']),
            'product_name' => $this->faker->name(),
            'discount' => $this->faker->randomDigit(),
            'bill_id' => Bill::query()->inRandomOrder()->value('id'),
            'user_id' => User::query()->inRandomOrder()->value('id'),
            'product_id' => Product::query()->inRandomOrder()->value('id'),
        ];
    }
}
