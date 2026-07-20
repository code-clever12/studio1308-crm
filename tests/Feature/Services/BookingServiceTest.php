<?php

use App\Exceptions\BookingUnavailableException;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;

function bookingNextMonday(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

beforeEach(function () {
    $this->bookingService = app(BookingService::class);

    $this->salon = Salon::factory()->create([
        'opens_at' => '09:00:00',
        'closes_at' => '18:00:00',
        'deposit_percentage' => 25,
        'sales_tax_rate' => 10,
    ]);

    $this->customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

    $this->service = Service::factory()->create([
        'price' => 100,
        'duration_minutes' => 60,
        'buffer_time_minutes' => 15,
        'is_active' => true,
        'requires_consent_form' => false,
        'deposit_amount' => null,
    ]);

    $this->staff = Staff::factory()->create(['status' => 'active']);
    $this->staff->services()->attach($this->service->id, ['is_available' => true]);

    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => Carbon::parse(bookingNextMonday())->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'break_start' => null,
        'break_end' => null,
        'is_working_day' => true,
    ]);
});

it('books an appointment with correct pricing and pending status', function () {
    $appointment = $this->bookingService->book(
        $this->customer,
        $this->service,
        $this->staff,
        bookingNextMonday(),
        '10:00',
    );

    expect($appointment->status)->toBe('pending')
        ->and($appointment->payment_status)->toBe('pending')
        ->and((float) $appointment->service_price)->toBe(100.0)
        ->and((float) $appointment->sales_tax_amount)->toBe(10.0)
        ->and((float) $appointment->total_amount)->toBe(110.0)
        ->and($appointment->staff_id)->toBe($this->staff->id)
        ->and($appointment->end_time)->toBe('11:15:00');
});

it('auto-selects an available staff member when none is specified', function () {
    $appointment = $this->bookingService->book(
        $this->customer,
        $this->service,
        null,
        bookingNextMonday(),
        '11:00',
    );

    expect($appointment->staff_id)->toBe($this->staff->id);
});

it('rejects a double-booking for the same staff/time', function () {
    $this->bookingService->book($this->customer, $this->service, $this->staff, bookingNextMonday(), '10:00');

    $anotherCustomer = User::factory()->create(['role' => 'customer']);

    $this->bookingService->book($anotherCustomer, $this->service, $this->staff, bookingNextMonday(), '10:00');
})->throws(BookingUnavailableException::class);

it('rejects booking a date in the past', function () {
    $this->bookingService->book(
        $this->customer,
        $this->service,
        $this->staff,
        Carbon::yesterday()->toDateString(),
        '10:00',
    );
})->throws(InvalidArgumentException::class);

it('rejects booking for a blocked (inactive) customer', function () {
    $this->customer->update(['is_active' => false]);

    $this->bookingService->book($this->customer, $this->service, $this->staff, bookingNextMonday(), '10:00');
})->throws(InvalidArgumentException::class);

it('requires consent form responses when the service demands one', function () {
    $this->service->update(['requires_consent_form' => true]);

    $this->bookingService->book($this->customer, $this->service, $this->staff, bookingNextMonday(), '10:00');
})->throws(InvalidArgumentException::class);

it('allows joining a waitlist for a fully booked slot', function () {
    $waitlist = $this->bookingService->joinWaitlist(
        $this->customer,
        $this->service,
        $this->staff,
        bookingNextMonday(),
    );

    expect($waitlist->status)->toBe('waiting')
        ->and($waitlist->customer_id)->toBe($this->customer->id)
        ->and($waitlist->service_id)->toBe($this->service->id);
});
