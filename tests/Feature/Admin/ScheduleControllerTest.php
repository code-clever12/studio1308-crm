<?php

use App\Models\Staff;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->staff = Staff::factory()->create();
});

it('lets an admin bulk-update a staff member\'s weekly schedule', function () {
    $schedules = collect(range(0, 6))->map(fn ($day) => [
        'day_of_week' => $day,
        'is_working_day' => $day !== 0,
        'start_time' => $day !== 0 ? '09:00' : null,
        'end_time' => $day !== 0 ? '17:00' : null,
        'break_start' => null,
        'break_end' => null,
    ])->all();

    $this->actingAs($this->admin)
        ->put(route('admin.schedules.update', $this->staff), ['schedules' => $schedules])
        ->assertRedirect(route('admin.schedules.edit', $this->staff));

    expect($this->staff->schedules()->count())->toBe(7)
        ->and($this->staff->schedules()->where('day_of_week', 0)->first()->is_working_day)->toBeFalse()
        ->and($this->staff->schedules()->where('day_of_week', 1)->first()->start_time)->toBe('09:00:00');
});

it('rejects a working day schedule where end time is before start time', function () {
    $schedules = collect(range(0, 6))->map(fn ($day) => [
        'day_of_week' => $day,
        'is_working_day' => true,
        'start_time' => '17:00',
        'end_time' => '09:00',
    ])->all();

    $this->actingAs($this->admin)
        ->put(route('admin.schedules.update', $this->staff), ['schedules' => $schedules])
        ->assertSessionHasErrors('schedules.0.end_time');
});

it('lets an admin add and remove a day off', function () {
    $date = now()->addWeek()->toDateString();

    $this->actingAs($this->admin)
        ->post(route('admin.schedules.days-off.store', $this->staff), ['date' => $date, 'reason' => 'Vacation'])
        ->assertRedirect(route('admin.schedules.edit', $this->staff));

    $dayOff = $this->staff->daysOff()->first();
    expect($dayOff)->not->toBeNull();

    $this->actingAs($this->admin)
        ->delete(route('admin.schedules.days-off.destroy', [$this->staff, $dayOff->id]))
        ->assertRedirect(route('admin.schedules.edit', $this->staff));

    expect($this->staff->daysOff()->count())->toBe(0);
});
