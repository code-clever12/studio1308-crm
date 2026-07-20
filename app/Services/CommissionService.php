<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Staff;
use Carbon\CarbonInterface;

class CommissionService
{
    /**
     * Commission owed to staff for a single completed appointment.
     * Commission applies to the service price only, never the deposit or tip.
     */
    public function calculateCommission(Appointment $appointment): float
    {
        if ($appointment->status !== 'completed' || ! $appointment->staff) {
            return 0.0;
        }

        return round((float) $appointment->service_price * ((float) $appointment->staff->commission_rate / 100), 2);
    }

    public function totalCommissionForStaff(Staff $staff, CarbonInterface $from, CarbonInterface $to): float
    {
        $appointments = $staff->appointments()
            ->where('status', 'completed')
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        return round(
            $appointments->sum(fn (Appointment $appointment) => (float) $appointment->service_price)
                * ((float) $staff->commission_rate / 100),
            2
        );
    }

    /**
     * @return array{staff_id: int, appointments_completed: int, total_revenue: float, total_commission: float}
     */
    public function commissionReport(Staff $staff, CarbonInterface $from, CarbonInterface $to): array
    {
        $appointments = $staff->appointments()
            ->where('status', 'completed')
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $revenue = (float) $appointments->sum('service_price');

        return [
            'staff_id' => $staff->id,
            'appointments_completed' => $appointments->count(),
            'total_revenue' => round($revenue, 2),
            'total_commission' => round($revenue * ((float) $staff->commission_rate / 100), 2),
        ];
    }
}
