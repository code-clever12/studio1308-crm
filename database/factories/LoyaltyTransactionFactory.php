<?php

namespace Database\Factories;

use App\Models\LoyaltyPoints;
use App\Models\LoyaltyTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['earn', 'redeem']);

        return [
            'loyalty_points_id' => LoyaltyPoints::factory(),
            'appointment_id' => null,
            'type' => $type,
            'points' => $type === 'earn' ? fake()->numberBetween(5, 50) : -fake()->numberBetween(5, 50),
            'description' => $type === 'earn' ? 'Points earned from completed appointment' : 'Points redeemed for discount',
        ];
    }
}
