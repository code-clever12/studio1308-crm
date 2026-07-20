<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\LoyaltyTransaction;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalesTaxTransaction;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Tip;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();
        $customers = User::where('role', 'customer')->get();
        $staffList = Staff::all();
        $services = Service::all();

        if ($customers->isEmpty() || $staffList->isEmpty() || $services->isEmpty()) {
            return;
        }

        $taxRate = (float) $salon->sales_tax_rate;
        $depositPercentage = (float) $salon->deposit_percentage;

        // 20 completed appointments in the past, with payments/reviews/tips/loyalty.
        for ($i = 0; $i < 20; $i++) {
            $this->makeAppointment(
                $customers->random(),
                $staffList->random(),
                $services->random(),
                now()->subDays(rand(2, 90)),
                'completed',
                $taxRate,
                $depositPercentage,
            );
        }

        // 10 upcoming confirmed appointments with deposit paid only.
        for ($i = 0; $i < 10; $i++) {
            $this->makeAppointment(
                $customers->random(),
                $staffList->random(),
                $services->random(),
                now()->addDays(rand(1, 30)),
                'confirmed',
                $taxRate,
                $depositPercentage,
            );
        }

        // 3 cancelled appointments.
        for ($i = 0; $i < 3; $i++) {
            $this->makeAppointment(
                $customers->random(),
                $staffList->random(),
                $services->random(),
                now()->subDays(rand(1, 20)),
                'cancelled',
                $taxRate,
                $depositPercentage,
            );
        }

        // 2 no-show appointments with the no-show fee charged.
        for ($i = 0; $i < 2; $i++) {
            $this->makeAppointment(
                $customers->random(),
                $staffList->random(),
                $services->random(),
                now()->subDays(rand(1, 15)),
                'no_show',
                $taxRate,
                $depositPercentage,
                (float) $salon->no_show_fee,
            );
        }

        // A few waitlist entries for fully booked slots.
        for ($i = 0; $i < 3; $i++) {
            Waitlist::create([
                'customer_id' => $customers->random()->id,
                'staff_id' => $staffList->random()->id,
                'service_id' => $services->random()->id,
                'requested_date' => now()->addDays(rand(1, 14))->toDateString(),
                'time_preference' => fake()->randomElement(['09:00:00', '13:00:00', '16:00:00']),
                'status' => 'waiting',
            ]);
        }
    }

    private function makeAppointment(
        User $customer,
        Staff $staff,
        Service $service,
        \DateTimeInterface|string $date,
        string $status,
        float $taxRate,
        float $depositPercentage,
        ?float $noShowFee = null,
    ): void {
        $servicePrice = (float) $service->price;
        $subtotal = $servicePrice;
        $salesTax = $service->is_taxable ? round($subtotal * $taxRate / 100, 2) : 0;
        $totalAmount = round($subtotal + $salesTax, 2);
        $depositPaid = round($subtotal * $depositPercentage / 100, 2);
        $startTime = fake()->randomElement(['09:00:00', '10:30:00', '13:00:00', '15:30:00', '16:30:00']);
        $endTime = date('H:i:s', strtotime($startTime) + $service->duration_minutes * 60 + $service->buffer_time_minutes * 60);

        $attributes = [
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_id' => $service->id,
            'appointment_date' => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'service_price' => $servicePrice,
            'subtotal' => $subtotal,
            'sales_tax_amount' => $salesTax,
            'deposit_percentage' => $depositPercentage,
            'total_amount' => $totalAmount,
        ];

        $tipAmount = 0;

        switch ($status) {
            case 'completed':
                $tipAmount = fake()->boolean(70) ? round($servicePrice * fake()->randomElement([0.15, 0.18, 0.20]), 2) : 0;
                $attributes = array_merge($attributes, [
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'deposit_paid' => $totalAmount,
                    'remaining_balance' => 0,
                    'tip_amount' => $tipAmount,
                ]);
                break;
            case 'cancelled':
                $cancellationFee = 25;
                $attributes = array_merge($attributes, [
                    'status' => 'cancelled',
                    'payment_status' => 'refunded',
                    'deposit_paid' => $depositPaid,
                    'remaining_balance' => 0,
                    'cancellation_fee' => $cancellationFee,
                    'cancellation_reason' => 'Customer requested cancellation',
                    'cancelled_at' => now(),
                ]);
                break;
            case 'no_show':
                $attributes = array_merge($attributes, [
                    'status' => 'no_show',
                    'payment_status' => 'paid',
                    'deposit_paid' => $depositPaid,
                    'remaining_balance' => round($totalAmount - $depositPaid, 2),
                    'no_show_fee_charged' => true,
                ]);
                break;
            default: // confirmed / upcoming
                $attributes = array_merge($attributes, [
                    'status' => 'confirmed',
                    'payment_status' => 'partial',
                    'deposit_paid' => $depositPaid,
                    'remaining_balance' => round($totalAmount - $depositPaid, 2),
                ]);
        }

        $appointment = Appointment::create($attributes);

        // Deposit / full payment via Stripe.
        Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => $appointment->deposit_paid,
            'breakdown_json' => [
                'service_price' => $servicePrice,
                'tax' => $salesTax,
                'tip' => $tipAmount,
                'total' => $appointment->deposit_paid,
            ],
            'payment_method' => 'stripe_card',
            'stripe_payment_intent_id' => 'pi_'.fake()->unique()->bothify('##########????????'),
            'stripe_charge_id' => 'ch_'.fake()->bothify('##########????????'),
            'status' => $status === 'cancelled' ? 'refunded' : 'succeeded',
            'refund_amount' => $status === 'cancelled' ? round($depositPaid - 25, 2) : null,
            'refund_reason' => $status === 'cancelled' ? 'cancellation' : null,
            'refund_date' => $status === 'cancelled' ? now() : null,
            'payment_date' => $appointment->created_at,
        ]);

        // No-show fee charged as a separate payment against the card on file.
        if ($status === 'no_show' && $noShowFee) {
            Payment::create([
                'appointment_id' => $appointment->id,
                'customer_id' => $customer->id,
                'amount' => $noShowFee,
                'breakdown_json' => ['no_show_fee' => $noShowFee],
                'payment_method' => 'stripe_card',
                'stripe_payment_intent_id' => 'pi_'.fake()->unique()->bothify('##########????????'),
                'status' => 'succeeded',
                'payment_date' => now(),
            ]);
        }

        if ($salesTax > 0) {
            SalesTaxTransaction::create([
                'appointment_id' => $appointment->id,
                'state' => 'NY',
                'tax_rate' => $taxRate,
                'taxable_amount' => $subtotal,
                'tax_amount' => $salesTax,
            ]);
        }

        if ($tipAmount > 0) {
            Tip::create([
                'appointment_id' => $appointment->id,
                'staff_id' => $staff->id,
                'customer_id' => $customer->id,
                'amount' => $tipAmount,
                'percentage' => round($tipAmount / $servicePrice * 100),
                'paid_via_stripe' => true,
                'status' => 'completed',
            ]);
        }

        if ($status === 'completed') {
            if (fake()->boolean(80)) {
                Review::create([
                    'appointment_id' => $appointment->id,
                    'customer_id' => $customer->id,
                    'staff_id' => $staff->id,
                    'rating' => fake()->numberBetween(3, 5),
                    'comment' => fake()->paragraph(),
                    'is_verified_booking' => true,
                    'helpful_count' => fake()->numberBetween(0, 15),
                ]);
            }

            $loyaltyPoints = $customer->loyaltyPoints;

            if ($loyaltyPoints) {
                $pointsEarned = (int) floor($servicePrice);

                LoyaltyTransaction::create([
                    'loyalty_points_id' => $loyaltyPoints->id,
                    'appointment_id' => $appointment->id,
                    'type' => 'earn',
                    'points' => $pointsEarned,
                    'description' => "Points earned from {$service->name}",
                ]);
            }
        }
    }
}
