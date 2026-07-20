<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $servicePrice = fake()->randomElement([35, 45, 60, 75, 90, 120, 150]);
        $taxRate = 8.875;
        $salesTax = round($servicePrice * $taxRate / 100, 2);
        $subtotal = $servicePrice;
        $depositPercentage = 25;
        $depositPaid = round($subtotal * $depositPercentage / 100, 2);
        $totalAmount = $subtotal + $salesTax;
        $startTime = fake()->randomElement(['09:00:00', '10:30:00', '13:00:00', '15:30:00']);

        return [
            'customer_id' => User::factory(),
            'staff_id' => Staff::factory(),
            'service_id' => Service::factory(),
            'appointment_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => date('H:i:s', strtotime($startTime) + 3600),
            'status' => 'confirmed',
            'service_price' => $servicePrice,
            'subtotal' => $subtotal,
            'sales_tax_amount' => $salesTax,
            'deposit_paid' => $depositPaid,
            'deposit_percentage' => $depositPercentage,
            'total_amount' => $totalAmount,
            'remaining_balance' => round($totalAmount - $depositPaid, 2),
            'cancellation_fee' => null,
            'cancellation_reason' => null,
            'notes' => null,
            'payment_status' => 'partial',
            'stripe_payment_intent_id' => null,
            'tip_amount' => 0,
            'no_show_fee_charged' => false,
            'reminder_sent' => false,
            'cancelled_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'deposit_paid' => $attributes['total_amount'] ?? 0,
            'remaining_balance' => 0,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => 'refunded',
            'cancellation_fee' => 25,
            'cancellation_reason' => 'Customer requested cancellation',
            'cancelled_at' => now(),
        ]);
    }
}
