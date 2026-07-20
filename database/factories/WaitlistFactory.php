<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Waitlist>
 */
class WaitlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'staff_id' => Staff::factory(),
            'service_id' => Service::factory(),
            'requested_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'time_preference' => fake()->randomElement(['09:00:00', '13:00:00', '16:00:00', null]),
            'status' => 'waiting',
            'notification_sent_at' => null,
            'converted_appointment_id' => null,
        ];
    }
}
