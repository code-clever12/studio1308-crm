<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 60);

        return [
            'name' => fake()->randomElement([
                'Shampoo', 'Conditioner', 'Hair Serum', 'Nail Polish', 'Face Cream', 'Body Lotion', 'Hair Oil',
            ]).' - '.fake()->word(),
            'description' => fake()->sentence(),
            'price' => $price,
            'cost' => round($price * 0.5, 2),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'stock' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 10,
            'image' => null,
            'is_active' => true,
        ];
    }
}
