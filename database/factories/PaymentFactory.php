<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 20, 200);

        return [
            'appointment_id' => Appointment::factory(),
            'customer_id' => User::factory(),
            'coupon_id' => null,
            'amount' => $amount,
            'breakdown_json' => [
                'service_price' => $amount,
                'tax' => 0,
                'tip' => 0,
                'total' => $amount,
            ],
            'payment_method' => 'stripe_card',
            'stripe_payment_intent_id' => 'pi_'.fake()->unique()->bothify('##########????????'),
            'stripe_charge_id' => 'ch_'.fake()->bothify('##########????????'),
            'currency' => 'USD',
            'status' => 'succeeded',
            'refund_amount' => null,
            'refund_reason' => null,
            'refund_date' => null,
            'refund_stripe_id' => null,
            'payment_date' => now(),
        ];
    }
}
