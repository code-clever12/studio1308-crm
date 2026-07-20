<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed_amount']);

        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'description' => fake()->sentence(),
            'discount_type' => $type,
            'discount_value' => $type === 'percentage' ? fake()->randomElement([10, 15, 20, 25]) : fake()->randomElement([5, 10, 15, 25]),
            'max_uses' => fake()->randomElement([50, 100, null]),
            'current_uses' => 0,
            'min_booking_amount' => fake()->randomElement([0, 25, 50, null]),
            'valid_from' => now()->subDays(7)->toDateString(),
            'valid_until' => now()->addMonths(2)->toDateString(),
            'is_active' => true,
        ];
    }
}
