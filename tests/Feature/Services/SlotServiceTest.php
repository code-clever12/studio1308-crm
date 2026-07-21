<?php

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Services\SlotService;
use Carbon\Carbon;

function nextMonday(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

beforeEach(function () {
    $this->slotService = app(SlotService::class);
    $this->staff = Staff::factory()->create();
    $this->service = Service::factory()->create([
        'duration_minutes' => 60,
        'buffer_time_minutes' => 15,
    ]);
});

it('returns no slots when staff has no schedule for that day', function () {
    $slots = $this->slotService->getAvailableSlots($this->staff, nextMonday(), $this->service);

    expect($slots)->toBe([]);
});

it('returns slots within working hours, excluding breaks', function () {
    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => Carbon::parse(nextMonday())->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '13:00:00',
        'break_start' => '10:00:00',
        'break_end' => '10:30:00',
        'is_working_day' => true,
    ]);

    // 60min service + 15min buffer = 75min slots, 15min interval.
    // Only post-break slots (>= 10:30) can fit without overlapping the break.
    $slots = $this->slotService->getAvailableSlots($this->staff, nextMonday(), $this->service);

    expect($slots)->not->toBeEmpty();

    foreach ($slots as $slot) {
        $slotStart = Carbon::parse(nextMonday().' '.$slot);
        $slotEnd = $slotStart->copy()->addMinutes(75);

        expect($slotStart->lt(Carbon::parse(nextMonday().' 10:00:00')) && $slotEnd->gt(Carbon::parse(nextMonday().' 10:00:00')))->toBeFalse();
    }
});

it('excludes slots overlapping an existing pending or confirmed appointment', function () {
    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => Carbon::parse(nextMonday())->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'break_start' => null,
        'break_end' => null,
        'is_working_day' => true,
    ]);

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => nextMonday(),
        'start_time' => '09:00:00',
        'end_time' => '10:15:00',
        'status' => 'confirmed',
    ]);

    expect($this->slotService->isRangeAvailable($this->staff, nextMonday(), '09:00:00', '10:15:00'))->toBeFalse();
    expect($this->slotService->isRangeAvailable($this->staff, nextMonday(), '10:15:00', '11:30:00'))->toBeTrue();
});

it('returns no slots on a day off', function () {
    StaffSchedule::factory()->create([
        'staff_id' => $this->staff->id,
        'day_of_week' => Carbon::parse(nextMonday())->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_working_day' => true,
    ]);

    $this->staff->daysOff()->create(['date' => nextMonday(), 'reason' => 'Vacation']);

    expect($this->slotService->getAvailableSlots($this->staff, nextMonday(), $this->service))->toBe([]);
});

it('returns the busy intervals for existing pending/confirmed appointments on a date', function () {
    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => nextMonday(),
        'start_time' => '09:00:00',
        'end_time' => '10:15:00',
        'status' => 'confirmed',
    ]);

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => nextMonday(),
        'start_time' => '11:00:00',
        'end_time' => '12:00:00',
        'status' => 'cancelled',
    ]);

    $busy = $this->slotService->getBusyIntervals($this->staff, nextMonday());

    expect($busy)->toHaveCount(1)
        ->and($busy->first()['start']->format('H:i'))->toBe('09:00')
        ->and($busy->first()['end']->format('H:i'))->toBe('10:15');
});

it('invalidates the cached busy intervals so a newly booked appointment is reflected', function () {
    $this->slotService->getBusyIntervals($this->staff, nextMonday());

    Appointment::factory()->create([
        'staff_id' => $this->staff->id,
        'appointment_date' => nextMonday(),
        'start_time' => '09:00:00',
        'end_time' => '10:15:00',
        'status' => 'confirmed',
    ]);

    $this->slotService->invalidate($this->staff, nextMonday());

    expect($this->slotService->getBusyIntervals($this->staff, nextMonday()))->toHaveCount(1);
});
