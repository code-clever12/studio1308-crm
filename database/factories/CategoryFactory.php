<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Hair', 'Nails', 'Skincare', 'Massage', 'Makeup', 'Waxing', 'Barbering', 'Lashes & Brows',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(8),
            'description' => fake()->sentence(),
            'icon' => null,
            'display_order' => fake()->numberBetween(0, 10),
        ];
    }
}
