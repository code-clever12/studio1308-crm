<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancellationService
{
    /**
     * Cancellations within this many hours of the appointment start incur the fee.
     */
    private const LATE_CANCELLATION_WINDOW_HOURS = 24;

    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly NotificationService $notificationService,
        private readonly SlotService $slotService,
    ) {
    }

    /**
     * Fee charged for a late cancellation. The salon's cancellation_policy
     * field is a freeform description for display; the actual computable
     * rule (24-hour window, dollar amount) reuses the same figure as the
     * no-show fee since the schema doesn't have a dedicated field for it.
     */
    public function calculateCancellationFee(Appointment $appointment, ?Salon $salon = null): float
    {
        $salon ??= Salon::query()->firstOrFail();

        $appointmentStart = Carbon::parse("{$appointment->appointment_date->toDateString()} {$appointment->start_time}");

        if (now()->diffInHours($appointmentStart, false) >= self::LATE_CANCELLATION_WINDOW_HOURS) {
            return 0.0;
        }

        return round((float) $salon->no_show_fee, 2);
    }

    /**
     * Cancel an appointment: computes the fee, refunds the deposit (minus fee)
     * if a real Stripe payment was captured, frees the slot, and notifies the
     * customer plus anyone waitlisted for a matching slot.
     */
    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            throw new InvalidArgumentException('Only pending or confirmed appointments can be cancelled.');
        }

        return DB::transaction(function () use ($appointment, $reason) {
            $fee = $this->calculateCancellationFee($appointment);
            $refundAmount = max(round((float) $appointment->deposit_paid - $fee, 2), 0);

            $payment = $appointment->payments()->where('status', 'succeeded')->latest()->first();

            $paymentStatus = $appointment->payment_status;

            if ($payment && $refundAmount > 0) {
                $this->paymentService->refund($payment, $refundAmount, 'cancellation');
                $paymentStatus = 'refunded';
            }

            $appointment->update([
                'status' => 'cancelled',
                'payment_status' => $paymentStatus,
                'cancellation_fee' => $fee,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            if ($appointment->staff_id) {
                $this->slotService->invalidate($appointment->staff, $appointment->appointment_date->toDateString());
            }

            $this->notifyMatchingWaitlist($appointment);
            $this->notificationService->sendCancellationConfirmation($appointment);

            return $appointment->fresh(['service', 'staff.user', 'customer']);
        });
    }

    /**
     * Notify waiting customers whose requested date is on or before the
     * freed-up date, for the same service, per the spec's matching rule.
     */
    private function notifyMatchingWaitlist(Appointment $appointment): void
    {
        Waitlist::query()
            ->where('service_id', $appointment->service_id)
            ->where('status', 'waiting')
            ->where('requested_date', '<=', $appointment->appointment_date)
            ->get()
            ->each(function (Waitlist $entry) {
                $entry->update([
                    'status' => 'notified',
                    'notification_sent_at' => now(),
                ]);

                $this->notificationService->sendWaitlistNotification($entry);
            });
    }
}
