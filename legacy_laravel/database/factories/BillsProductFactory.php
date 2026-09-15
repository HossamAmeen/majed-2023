<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillsProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillsProduct>
 */
class BillsProductFactory extends Factory
{
    protected $model = BillsProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_price' => $this->faker->numberBetween(100, 500),
            'selling_price' => $this->faker->numberBetween(200, 600),
            'discount' => $this->faker->numberBetween(20, 80),
            'quantity' => $this->faker->numberBetween(1, 8),
            'product_id' => Product::query()->inRandomOrder()->value('id'),
            'bill_id' => Bill::query()->inRandomOrder()->value('id'),
        ];
    }
}
