<?php

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use App\Notifications\StaffAssigned;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

it('notifies the assigned staff member when a booking is made', function () {
    Notification::fake();

    Salon::factory()->create(['opens_at' => '09:00:00', 'closes_at' => '18:00:00']);

    $customer = User::factory()->create(['role' => 'customer']);
    $service = Service::factory()->create(['duration_minutes' => 60, 'buffer_time_minutes' => 15]);
    $staff = Staff::factory()->create(['status' => 'active']);
    $staff->services()->attach($service->id, ['is_available' => true]);

    $date = Carbon::now()->next(Carbon::MONDAY)->toDateString();

    StaffSchedule::factory()->create([
        'staff_id' => $staff->id,
        'day_of_week' => Carbon::parse($date)->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_working_day' => true,
    ]);

    app(BookingService::class)->book($customer, $service, $staff, $date, '10:00');

    Notification::assertSentTo($staff->user, StaffAssigned::class);
});
