<?php

namespace App\Jobs;

use App\Services\NoShowFeeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Scheduled every few minutes (see routes/console.php). Finds appointments
 * 30+ minutes past their start time and still pending/confirmed, marks each
 * as a no-show, and queues the fee charge for any with a card on file.
 */
class MarkNoShowAppointments implements ShouldQueue
{
    use Queueable;

    public function handle(NoShowFeeService $noShowFeeService): void
    {
        foreach ($noShowFeeService->findEligibleAppointments() as $appointment) {
            $noShowFeeService->markNoShow($appointment);

            ChargeNoShowFee::dispatch($appointment);
        }
    }
}
