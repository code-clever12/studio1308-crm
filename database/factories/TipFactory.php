<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tip>
 */
class TipFactory extends Factory
{
    public function definition(): array
    {
        $percentage = fake()->randomElement([15, 18, 20]);

        return [
            'appointment_id' => Appointment::factory(),
            'staff_id' => Staff::factory(),
            'customer_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 5, 30),
            'percentage' => $percentage,
            'paid_via_stripe' => true,
            'status' => 'completed',
        ];
    }
}
