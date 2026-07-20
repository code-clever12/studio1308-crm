<?php

namespace Database\Factories;

use App\Models\ACHBankAccount;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ACHBankAccount>
 */
class ACHBankAccountFactory extends Factory
{
    public function definition(): array
    {
        $accountNumber = fake()->numerify('##########');

        return [
            'staff_id' => Staff::factory(),
            'bank_account_routing_number' => fake()->numerify('#########'),
            'bank_account_number' => $accountNumber,
            'bank_account_holder_name' => fake()->name(),
            'bank_name' => fake()->randomElement(['Chase', 'Bank of America', 'Wells Fargo', 'Citibank']),
            'verification_status' => 'verified',
            'stripe_bank_account_token' => null,
            'last_4_digits' => substr($accountNumber, -4),
        ];
    }
}
