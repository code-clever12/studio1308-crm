<?php

namespace App\Services;

use App\Events\AppointmentBooked;
use App\Exceptions\BookingUnavailableException;
use App\Models\Appointment;
use App\Models\AppointmentFormResponse;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private readonly SlotService $slotService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Book an appointment for a customer, validating availability and business
     * rules, and computing pricing. Payment is NOT processed here — the
     * appointment is created with status/payment_status = 'pending'; Step 8's
     * payment flow confirms it once the Stripe charge succeeds (which is also
     * when the booking-confirmation notification fires, per spec).
     *
     * @param  array<int, array<string, mixed>>  $formResponses  [consent_form_id => [field_id => answer]]
     *
     * @throws InvalidArgumentException|BookingUnavailableException
     */
    public function book(
        User $customer,
        Service $service,
        ?Staff $staff,
        string $date,
        string $startTime,
        array $formResponses = [],
    ): Appointment {
        // Normalize to H:i:s — MySQL silently pads a bare "H:i" TIME value on write,
        // but SQLite (used in tests) stores it verbatim, causing inconsistent
        // string comparisons against already-normalized values.
        $startTime = Carbon::parse($startTime)->format('H:i:s');

        if (! $customer->is_active) {
            throw new InvalidArgumentException('Your account has booking restrictions. Contact support.');
        }

        if (! $service->is_active) {
            throw new InvalidArgumentException('This service is not currently available.');
        }

        if (Carbon::parse($date)->startOfDay()->isPast()) {
            throw new InvalidArgumentException('The selected date is in the past.');
        }

        if ($service->requires_consent_form && empty($formResponses)) {
            throw new InvalidArgumentException('This service requires a completed consent form before booking.');
        }

        $salon = Salon::query()->firstOrFail();
        $endTime = Carbon::parse($startTime)
            ->addMinutes($service->duration_minutes + $service->buffer_time_minutes)
            ->format('H:i:s');

        if (! $this->withinOperatingHours($salon, $startTime, $endTime)) {
            throw new InvalidArgumentException("The selected time is outside the salon's operating hours.");
        }

        return DB::transaction(function () use ($customer, $service, $staff, $date, $startTime, $endTime, $formResponses, $salon) {
            $resolvedStaff = $staff
                ? $this->assertStaffAvailable($staff, $service, $date, $startTime, $endTime)
                : $this->findAnyAvailableStaff($service, $date, $startTime, $endTime);

            $breakdown = $this->paymentService->calculateBreakdown($service, 0, $salon);

            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'staff_id' => $resolvedStaff?->id,
                'service_id' => $service->id,
                'appointment_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'pending',
                'service_price' => $breakdown['subtotal'],
                'subtotal' => $breakdown['subtotal'],
                'sales_tax_amount' => $breakdown['tax'],
                'deposit_percentage' => $salon->deposit_percentage,
                'total_amount' => $breakdown['total_amount'],
                'remaining_balance' => $breakdown['remaining_balance'],
                'payment_status' => 'pending',
            ]);

            $this->saveFormResponses($appointment, $formResponses);

            if ($resolvedStaff) {
                $this->slotService->invalidate($resolvedStaff, $date);
            }

            $appointment = $appointment->fresh(['service', 'staff.user', 'customer']);

            AppointmentBooked::dispatch($appointment);

            return $appointment;
        });
    }

    /**
     * Join the waitlist when no slot is available for a preferred date/staff.
     * NotifyMatchingWaitlist (listening for AppointmentCancelled) notifies
     * matching entries when a spot opens up.
     */
    public function joinWaitlist(User $customer, Service $service, ?Staff $staff, string $requestedDate, ?string $timePreference = null): Waitlist
    {
        if (! $customer->is_active) {
            throw new InvalidArgumentException('Your account has booking restrictions. Contact support.');
        }

        return Waitlist::create([
            'customer_id' => $customer->id,
            'staff_id' => $staff?->id,
            'service_id' => $service->id,
            'requested_date' => $requestedDate,
            'time_preference' => $timePreference,
            'status' => 'waiting',
        ]);
    }

    private function assertStaffAvailable(Staff $staff, Service $service, string $date, string $startTime, string $endTime): Staff
    {
        if (! $staff->services->contains('id', $service->id)) {
            throw new BookingUnavailableException('This staff member does not offer the selected service.');
        }

        if ($this->slotService->isDayOff($staff, $date)) {
            throw new BookingUnavailableException('This staff member is not available on the selected date.');
        }

        if (! $this->slotService->isRangeAvailable($staff, $date, $startTime, $endTime)) {
            throw new BookingUnavailableException('This time slot is no longer available.');
        }

        return $staff;
    }

    private function findAnyAvailableStaff(Service $service, string $date, string $startTime, string $endTime): Staff
    {
        $candidates = $service->staff()
            ->wherePivot('is_available', true)
            ->where('status', 'active')
            ->get();

        foreach ($candidates as $candidate) {
            if (! $this->slotService->isDayOff($candidate, $date)
                && $this->slotService->isRangeAvailable($candidate, $date, $startTime, $endTime)) {
                return $candidate;
            }
        }

        throw new BookingUnavailableException('No staff member is available for this service at the selected time.');
    }

    private function withinOperatingHours(Salon $salon, string $startTime, string $endTime): bool
    {
        $opens = Carbon::parse($salon->opens_at)->format('H:i:s');
        $closes = Carbon::parse($salon->closes_at)->format('H:i:s');

        return $startTime >= $opens && $endTime <= $closes;
    }

    /**
     * @param  array<int, array<string, mixed>>  $formResponses
     */
    private function saveFormResponses(Appointment $appointment, array $formResponses): void
    {
        foreach ($formResponses as $consentFormId => $data) {
            AppointmentFormResponse::create([
                'appointment_id' => $appointment->id,
                'consent_form_id' => $consentFormId,
                'form_data_json' => $data,
            ]);
        }
    }
}
