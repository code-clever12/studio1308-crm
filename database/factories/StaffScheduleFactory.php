<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffSchedule>
 */
class StaffScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
            'is_working_day' => true,
        ];
    }
}
