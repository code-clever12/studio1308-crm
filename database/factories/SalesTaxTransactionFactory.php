<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\SalesTaxTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesTaxTransaction>
 */
class SalesTaxTransactionFactory extends Factory
{
    public function definition(): array
    {
        $taxableAmount = fake()->randomFloat(2, 20, 200);
        $taxRate = 8.875;

        return [
            'appointment_id' => Appointment::factory(),
            'state' => fake()->stateAbbr(),
            'tax_rate' => $taxRate,
            'taxable_amount' => $taxableAmount,
            'tax_amount' => round($taxableAmount * $taxRate / 100, 2),
        ];
    }
}
