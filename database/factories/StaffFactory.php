<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->staff(),
            'bio' => fake()->paragraph(),
            'photo' => null,
            'commission_rate' => fake()->randomElement([15, 20, 25, 30]),
            'status' => 'active',
            'hire_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
            'bank_account_routing_number' => null,
            'bank_account_number' => null,
            'bank_account_holder_name' => null,
            'stripe_connect_account_id' => null,
        ];
    }
}
