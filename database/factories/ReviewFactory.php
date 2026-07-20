<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Review;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory()->completed(),
            'customer_id' => User::factory(),
            'staff_id' => Staff::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->paragraph(),
            'is_verified_booking' => true,
            'helpful_count' => fake()->numberBetween(0, 25),
        ];
    }
}
