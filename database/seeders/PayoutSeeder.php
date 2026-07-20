<?php

namespace Database\Seeders;

use App\Models\ACHPayout;
use App\Models\Staff;
use App\Models\Tip;
use Illuminate\Database\Seeder;

class PayoutSeeder extends Seeder
{
    public function run(): void
    {
        Staff::whereHas('achBankAccount')->get()->each(function (Staff $staff) {
            $commission = $staff->appointments()
                ->where('status', 'completed')
                ->get()
                ->sum(fn ($appointment) => (float) $appointment->service_price * ((float) $staff->commission_rate / 100));

            $tips = Tip::where('staff_id', $staff->id)->where('status', 'completed')->sum('amount');

            if ($commission <= 0 && $tips <= 0) {
                return;
            }

            ACHPayout::create([
                'staff_id' => $staff->id,
                'amount' => round($commission + $tips, 2),
                'status' => 'completed',
                'stripe_payout_id' => 'po_'.fake()->bothify('##########????????'),
                'payout_date' => now()->subDays(7)->toDateString(),
                'expected_arrival_date' => now()->subDays(5)->toDateString(),
                'commission_amount' => round($commission, 2),
                'tips_amount' => round($tips, 2),
            ]);
        });
    }
}
