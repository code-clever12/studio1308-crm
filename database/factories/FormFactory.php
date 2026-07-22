<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Summer Offer 20%', 'New Client Special', 'Holiday Glow Package', 'Refer a Friend',
        ]).' #'.fake()->unique()->numberBetween(1, 100000);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
