<?php

namespace Database\Factories;

use App\Models\DayOff;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DayOff>
 */
class DayOffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'date' => fake()->dateTimeBetween('now', '+2 months'),
            'reason' => fake()->randomElement(['Vacation', 'Sick Leave', 'Personal']),
        ];
    }
}
