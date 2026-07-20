<?php

namespace Database\Factories;

use App\Models\LoyaltyPoints;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyPoints>
 */
class LoyaltyPointsFactory extends Factory
{
    protected $model = LoyaltyPoints::class;

    public function definition(): array
    {
        $earned = fake()->numberBetween(0, 1000);
        $redeemed = fake()->numberBetween(0, $earned);

        return [
            'customer_id' => User::factory(),
            'balance' => $earned - $redeemed,
            'earned_total' => $earned,
            'redeemed_total' => $redeemed,
            'last_activity_at' => now(),
        ];
    }
}
