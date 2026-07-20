<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $services = Service::inRandomOrder()->limit(2)->pluck('id');

        if ($services->isEmpty()) {
            $services = collect([Service::factory()->create()->id, Service::factory()->create()->id]);
        }

        $totalDuration = Service::whereIn('id', $services)->sum('duration_minutes');

        return [
            'name' => fake()->randomElement(['Bridal Package', 'Spa Day Package', 'Mani-Pedi Combo', 'Hair & Makeup Package']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 350),
            'services_json' => $services->values()->all(),
            'total_duration_minutes' => $totalDuration ?: 90,
            'discount_percentage' => fake()->randomElement([10, 15, 20, null]),
            'image' => null,
            'is_active' => true,
        ];
    }
}
