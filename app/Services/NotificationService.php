<?php

namespace App\Services;

use App\Models\ACHPayout;
use App\Models\Appointment;
use App\Models\FormSubmission;
use App\Models\Tip;
use App\Models\User;
use App\Models\Waitlist;
use App\Notifications\AdminDailySummary;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentReminder;
use App\Notifications\BookingConfirmed;
use App\Notifications\NewFormSubmissionReceived;
use App\Notifications\NoShowFeeCharged;
use App\Notifications\PayoutFailed;
use App\Notifications\StaffAssigned;
use App\Notifications\TipReceived;
use App\Notifications\WaitlistSlotAvailable;

/**
 * Thin wrapper around dispatching the app's Notification classes (see
 * app/Notifications/). Each notification is queued automatically
 * (ShouldQueue) and delivers via both the database channel (in-app alerts)
 * and mail (real Mailable/Blade templates in app/Mail + resources/views/emails).
 */
class NotificationService
{
    public function sendBookingConfirmation(Appointment $appointment): void
    {
        $appointment->customer->notify(new BookingConfirmed($appointment));

        if ($appointment->staff) {
            $this->sendStaffAssignment($appointment);
        }
    }

    public function sendAppointmentReminder(Appointment $appointment): void
    {
        $appointment->customer->notify(new AppointmentReminder($appointment));
    }

    public function sendCancellationConfirmation(Appointment $appointment): void
    {
        $appointment->customer->notify(new AppointmentCancelled($appointment));
    }

    public function sendWaitlistNotification(Waitlist $waitlist): void
    {
        $waitlist->customer->notify(new WaitlistSlotAvailable($waitlist));
    }

    public function sendStaffAssignment(Appointment $appointment): void
    {
        $appointment->staff?->user?->notify(new StaffAssigned($appointment));
    }

    public function sendNoShowFeeReceipt(Appointment $appointment, float $feeAmount): void
    {
        $appointment->customer->notify(new NoShowFeeCharged($appointment, $feeAmount));
    }

    public function sendTipReceipt(Tip $tip): void
    {
        $tip->staff?->user?->notify(new TipReceived($tip));
    }

    /**
     * @param  array{date: string, appointments_count: int, no_shows_count: int, cancellations_count: int, revenue: float}  $stats
     */
    public function sendAdminDailySummary(array $stats): void
    {
        User::where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new AdminDailySummary($stats))
        );
    }

    public function sendPayoutFailedAlert(ACHPayout $payout): void
    {
        User::where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new PayoutFailed($payout))
        );
    }

    public function sendNewFormSubmissionAlert(FormSubmission $submission): void
    {
        User::where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new NewFormSubmissionReceived($submission))
        );
    }
}
