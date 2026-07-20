<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Tip;
use App\Models\User;
use Carbon\CarbonInterface;

class TipService
{
    /** @var array<int, int> */
    private const DEFAULT_PERCENTAGES = [15, 18, 20];

    /**
     * Preset tip amounts for checkout, e.g. [15 => 11.25, 18 => 13.50, 20 => 15.00].
     *
     * @param  array<int, int>  $percentages
     * @return array<int, float>
     */
    public function calculateTipOptions(float $servicePrice, array $percentages = self::DEFAULT_PERCENTAGES): array
    {
        $options = [];

        foreach ($percentages as $percentage) {
            $options[$percentage] = round($servicePrice * $percentage / 100, 2);
        }

        return $options;
    }

    public function calculateCustomTip(float $servicePrice, float $percentage): float
    {
        return round($servicePrice * $percentage / 100, 2);
    }

    /**
     * Record a tip once the combined deposit+tip charge succeeds (Step 8 wires this
     * up to the Stripe payment confirmation webhook) and reflect it on the appointment.
     */
    public function recordTip(Appointment $appointment, float $amount, User $customer, ?float $percentage = null): ?Tip
    {
        if ($amount <= 0 || ! $appointment->staff_id) {
            return null;
        }

        $tip = Tip::create([
            'appointment_id' => $appointment->id,
            'staff_id' => $appointment->staff_id,
            'customer_id' => $customer->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'paid_via_stripe' => true,
            'status' => 'completed',
        ]);

        $appointment->update(['tip_amount' => $amount]);

        return $tip;
    }

    public function totalTipsForStaff(Staff $staff, ?CarbonInterface $from = null, ?CarbonInterface $to = null): float
    {
        $query = Tip::query()
            ->where('staff_id', $staff->id)
            ->where('status', 'completed');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return (float) $query->sum('amount');
    }
}
