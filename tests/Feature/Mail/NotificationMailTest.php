<?php

use App\Mail\AdminDailySummaryMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\CancellationNoticeMail;
use App\Mail\NewFormSubmissionMail;
use App\Mail\NoShowFeeChargedMail;
use App\Mail\PayoutFailedMail;
use App\Mail\WaitlistNotificationMail;
use App\Models\ACHPayout;
use App\Models\Appointment;
use App\Models\FormSubmission;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\Tip;
use App\Models\User;
use App\Models\Waitlist;
use App\Notifications\AdminDailySummary;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentReminder;
use App\Notifications\BookingConfirmed;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\NewFormSubmissionReceived;
use App\Notifications\NoShowFeeCharged;
use App\Notifications\PayoutFailed;
use App\Notifications\StaffAssigned;
use App\Notifications\TipReceived;
use App\Notifications\WaitlistSlotAvailable;
use App\Services\NotificationService;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

/*
|--------------------------------------------------------------------------
| Notification::fake() + assertSentTo() verifies each event dispatches the
| right Notification to the right recipient (matches the existing pattern
| in Events/AppointmentEventsTest.php). Mail::fake() is deliberately NOT
| used here: it only reliably intercepts the Mail::to()->send($mailable)
| call convention, not the Mailable::send($mailer) convention Laravel's
| MailChannel uses internally for a toMail() that returns a Mailable — so
| rendering/content is verified below by calling toMail() + render() directly.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    Salon::factory()->create([
        'name' => '1308Studio',
        'address' => '123 Main St',
        'city' => 'Austin',
        'state' => 'TX',
        'zip_code' => '78701',
        'cancellation_policy' => 'Free cancellation up to 24 hours before your appointment.',
    ]);

    $this->notificationService = app(NotificationService::class);
});

it('sends a booking confirmation email with an ICS calendar attachment', function () {
    Notification::fake();

    $appointment = Appointment::factory()->create([
        'total_amount' => 110,
        'deposit_paid' => 25,
        'remaining_balance' => 85,
    ]);

    $this->notificationService->sendBookingConfirmation($appointment);

    Notification::assertSentTo($appointment->customer, BookingConfirmed::class);

    $mail = (new BookingConfirmed($appointment))->toMail($appointment->customer);
    expect($mail)->toBeInstanceOf(BookingConfirmationMail::class)
        ->and($mail->hasTo($appointment->customer->email))->toBeTrue()
        ->and($mail->attachments())->toHaveCount(1);

    $html = $mail->render();
    expect($html)->toContain(e($appointment->service->name))
        ->and($html)->toContain('85.00');
});

it('also emails the assigned staff member when a booking is confirmed', function () {
    Notification::fake();

    $staff = Staff::factory()->create();
    $appointment = Appointment::factory()->create(['staff_id' => $staff->id]);

    $this->notificationService->sendBookingConfirmation($appointment);

    Notification::assertSentTo($staff->user, StaffAssigned::class);

    $mail = (new StaffAssigned($appointment))->toMail($staff->user);
    expect($mail->hasTo($staff->user->email))->toBeTrue();
});

it('sends an appointment reminder email', function () {
    Notification::fake();

    $appointment = Appointment::factory()->create();

    $this->notificationService->sendAppointmentReminder($appointment);

    Notification::assertSentTo($appointment->customer, AppointmentReminder::class);

    $mail = (new AppointmentReminder($appointment))->toMail($appointment->customer);
    expect($mail->hasTo($appointment->customer->email))->toBeTrue();

    $html = $mail->render();
    expect($html)->toContain(e($appointment->service->name));
});

it('sends a cancellation notice email with the refund amount', function () {
    Notification::fake();

    $appointment = Appointment::factory()->create([
        'deposit_paid' => 25,
        'cancellation_fee' => 10,
    ]);

    $this->notificationService->sendCancellationConfirmation($appointment);

    Notification::assertSentTo($appointment->customer, AppointmentCancelled::class);

    $html = (new CancellationNoticeMail($appointment))->render();
    expect($html)->toContain('15.00');
});

it('sends a waitlist notification email with a book-now link for the service', function () {
    Notification::fake();

    $waitlist = Waitlist::factory()->create();

    $this->notificationService->sendWaitlistNotification($waitlist);

    Notification::assertSentTo($waitlist->customer, WaitlistSlotAvailable::class);

    $mail = (new WaitlistSlotAvailable($waitlist))->toMail($waitlist->customer);
    expect($mail)->toBeInstanceOf(WaitlistNotificationMail::class)
        ->and($mail->hasTo($waitlist->customer->email))->toBeTrue();

    $html = $mail->render();
    expect($html)->toContain((string) $waitlist->service_id);
});

it('sends a no-show fee receipt email to the customer', function () {
    Notification::fake();

    $appointment = Appointment::factory()->create();

    $this->notificationService->sendNoShowFeeReceipt($appointment, 35.0);

    Notification::assertSentTo($appointment->customer, NoShowFeeCharged::class);

    $html = (new NoShowFeeChargedMail($appointment, 35.0))->render();
    expect($html)->toContain('35.00');
});

it('sends a tip receipt email to the staff member', function () {
    Notification::fake();

    $tip = Tip::factory()->create(['amount' => 12.5]);

    $this->notificationService->sendTipReceipt($tip);

    Notification::assertSentTo($tip->staff->user, TipReceived::class);

    $mail = (new TipReceived($tip))->toMail($tip->staff->user);
    expect($mail->hasTo($tip->staff->user->email))->toBeTrue();

    $html = $mail->render();
    expect($html)->toContain('12.50');
});

it('sends the admin daily summary email to every admin user', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $stats = [
        'date' => '2026-07-21',
        'appointments_count' => 5,
        'no_shows_count' => 1,
        'cancellations_count' => 2,
        'revenue' => 450.0,
    ];

    $this->notificationService->sendAdminDailySummary($stats);

    Notification::assertSentTo($admin, AdminDailySummary::class);

    $mail = (new AdminDailySummary($stats))->toMail($admin);
    expect($mail)->toBeInstanceOf(AdminDailySummaryMail::class)
        ->and($mail->hasTo($admin->email))->toBeTrue();
});

it('sends a payout-failed alert email to every admin user', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $payout = ACHPayout::factory()->create(['failure_reason' => 'Account closed']);

    $this->notificationService->sendPayoutFailedAlert($payout);

    Notification::assertSentTo($admin, PayoutFailed::class);

    $mail = (new PayoutFailed($payout))->toMail($admin);
    expect($mail)->toBeInstanceOf(PayoutFailedMail::class)
        ->and($mail->hasTo($admin->email))->toBeTrue();

    $html = $mail->render();
    expect($html)->toContain('Account closed');
});

it('sends a new-lead alert email to every admin user when a form is submitted', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $submission = FormSubmission::factory()->create([
        'payload' => ['full_name' => 'Jane Doe', 'phone_number' => '555-1234'],
        'value' => 49.99,
    ]);

    $this->notificationService->sendNewFormSubmissionAlert($submission);

    Notification::assertSentTo($admin, NewFormSubmissionReceived::class);

    $mail = (new NewFormSubmissionReceived($submission))->toMail($admin);
    expect($mail)->toBeInstanceOf(NewFormSubmissionMail::class)
        ->and($mail->hasTo($admin->email))->toBeTrue();

    $html = $mail->render();
    expect($html)->toContain('Jane Doe')
        ->and($html)->toContain('49.99');
});

it('only routes the database and push channels to real User accounts, never on-demand notifiables', function () {
    $admin = User::factory()->admin()->create();
    $submission = FormSubmission::factory()->create();
    $notification = new NewFormSubmissionReceived($submission);

    expect($notification->via($admin))->toBe(['database', 'mail', FcmChannel::class]);

    $anonymous = new AnonymousNotifiable;
    $anonymous->route('mail', 'sales@example.com');
    expect($notification->via($anonymous))->toBe(['mail']);
});

it('also emails extra addresses configured in the salon\'s lead notification settings', function () {
    Notification::fake();

    Salon::query()->first()->update([
        'lead_notification_emails' => 'sales@example.com, owner@example.com ,',
    ]);

    $admin = User::factory()->admin()->create();
    $submission = FormSubmission::factory()->create();

    $this->notificationService->sendNewFormSubmissionAlert($submission);

    Notification::assertSentTo($admin, NewFormSubmissionReceived::class);
    Notification::assertSentOnDemand(
        NewFormSubmissionReceived::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === ['sales@example.com', 'owner@example.com']
    );
});

it('skips the extra-address notification entirely when none are configured', function () {
    Notification::fake();

    // The beforeEach salon's factory-generated owner is itself an admin, so
    // exactly one notification (to that owner) is expected — none on-demand,
    // since lead_notification_emails is blank by default.
    $this->notificationService->sendNewFormSubmissionAlert(FormSubmission::factory()->create());

    Notification::assertSentTimes(NewFormSubmissionReceived::class, 1);
});

it('generates valid iCalendar content for the booking confirmation attachment', function () {
    $appointment = Appointment::factory()->create([
        'appointment_date' => '2026-08-18',
        'start_time' => '13:00:00',
        'end_time' => '14:00:00',
    ]);

    $ics = $appointment->toIcsContent();

    expect($ics)->toContain('BEGIN:VCALENDAR')
        ->toContain('BEGIN:VEVENT')
        ->toContain('DTSTART:20260818T130000')
        ->toContain('DTEND:20260818T140000')
        ->toContain('UID:appointment-'.$appointment->id.'@')
        ->toContain('END:VEVENT')
        ->toContain('END:VCALENDAR');
});
