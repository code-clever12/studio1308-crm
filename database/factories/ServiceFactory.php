<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomElement([35, 45, 60, 75, 90, 120, 150]);

        return [
            'category_id' => Category::factory(),
            'consent_form_id' => null,
            'name' => fake()->randomElement([
                "Women's Haircut", "Men's Haircut", 'Color & Highlights', 'Deep Conditioning',
                'Gel Manicure', 'Spa Pedicure', 'Swedish Massage', 'Facial Treatment',
                'Eyebrow Waxing', 'Full Body Wax', 'Bridal Makeup', 'Lash Extensions',
            ]),
            'description' => fake()->sentence(),
            'price' => $price,
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60, 90, 120]),
            'buffer_time_minutes' => 15,
            'deposit_amount' => null,
            'requires_consent_form' => false,
            'is_taxable' => true,
            'image' => null,
            'is_active' => true,
            'display_order' => fake()->numberBetween(0, 20),
        ];
    }
}
