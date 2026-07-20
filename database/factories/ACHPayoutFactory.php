<?php

namespace Database\Factories;

use App\Models\ACHPayout;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ACHPayout>
 */
class ACHPayoutFactory extends Factory
{
    protected $model = ACHPayout::class;

    public function definition(): array
    {
        $commission = fake()->randomFloat(2, 100, 800);
        $tips = fake()->randomFloat(2, 20, 150);

        return [
            'staff_id' => Staff::factory(),
            'amount' => round($commission + $tips, 2),
            'status' => 'completed',
            'stripe_payout_id' => 'po_'.fake()->bothify('##########????????'),
            'payout_date' => now()->subDays(3)->toDateString(),
            'expected_arrival_date' => now()->subDays(1)->toDateString(),
            'failure_reason' => null,
            'commission_amount' => $commission,
            'tips_amount' => $tips,
            'adjustments_amount' => null,
            'notes' => null,
        ];
    }
}
