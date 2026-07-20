<?php

namespace Database\Factories;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Salon>
 */
class SalonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->admin(),
            'name' => fake()->company().' Salon & Spa',
            'description' => fake()->paragraph(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->postcode(),
            'phone' => fake()->numerify('##########'),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'logo' => null,
            'timezone' => 'America/New_York',
            'opens_at' => '09:00:00',
            'closes_at' => '18:00:00',
            'cancellation_policy' => 'Free cancellation up to 24 hours before your appointment. Cancellations within 24 hours incur a $25 fee.',
            'deposit_percentage' => 25,
            'no_show_fee' => 25,
            'enable_tips' => true,
            'sales_tax_rate' => 8.875,
            'acct_stripe_connect_id' => null,
            'is_active' => true,
        ];
    }
}
