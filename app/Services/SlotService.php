<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Generates available booking slots for a staff member and detects overlaps.
 *
 * Busy intervals (existing appointments) are cached per staff/date for 5 minutes
 * rather than caching final per-service slot lists — this keeps invalidation
 * trivial (just staff_id + date) regardless of which service is being booked.
 */
class SlotService
{
    private const CACHE_TTL_MINUTES = 5;

    private const DEFAULT_INTERVAL_MINUTES = 15;

    /**
     * Available start times ("H:i") for the given staff/date/service.
     *
     * @return array<int, string>
     */
    public function getAvailableSlots(Staff $staff, string $date, Service $service, int $intervalMinutes = self::DEFAULT_INTERVAL_MINUTES): array
    {
        if ($this->isDayOff($staff, $date)) {
            return [];
        }

        $schedule = $staff->schedules->firstWhere('day_of_week', Carbon::parse($date)->dayOfWeek);

        if (! $schedule || ! $schedule->is_working_day || ! $schedule->start_time || ! $schedule->end_time) {
            return [];
        }

        $serviceMinutes = $service->duration_minutes + $service->buffer_time_minutes;

        $windowStart = Carbon::parse($date.' '.$schedule->start_time);
        $windowEnd = Carbon::parse($date.' '.$schedule->end_time);

        $breakStart = $schedule->break_start ? Carbon::parse($date.' '.$schedule->break_start) : null;
        $breakEnd = $schedule->break_end ? Carbon::parse($date.' '.$schedule->break_end) : null;

        $busyIntervals = $this->getBusyIntervals($staff, $date);

        $slots = [];
        $cursor = $windowStart->copy();

        while ($cursor->copy()->addMinutes($serviceMinutes)->lte($windowEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($serviceMinutes);

            $overlapsBreak = $breakStart && $breakEnd
                && $cursor->lt($breakEnd) && $slotEnd->gt($breakStart);

            $overlapsBusy = $busyIntervals->contains(
                fn (array $busy) => $cursor->lt($busy['end']) && $slotEnd->gt($busy['start'])
            );

            if (! $overlapsBreak && ! $overlapsBusy) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes($intervalMinutes);
        }

        return $slots;
    }

    /**
     * Busy (pending/confirmed) appointment intervals for a staff member on a date.
     *
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    public function getBusyIntervals(Staff $staff, string $date): Collection
    {
        $raw = Cache::remember($this->cacheKey($staff, $date), now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($staff, $date) {
            return $staff->appointments()
                ->whereDate('appointment_date', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->get(['start_time', 'end_time'])
                ->map(fn (Appointment $appointment) => [
                    'start' => (string) $appointment->start_time,
                    'end' => (string) $appointment->end_time,
                ])
                ->all();
        });

        return collect($raw)->map(fn (array $interval) => [
            'start' => Carbon::parse($date.' '.$interval['start']),
            'end' => Carbon::parse($date.' '.$interval['end']),
        ]);
    }

    /**
     * Whether [startTime, endTime) is free of overlapping pending/confirmed
     * appointments for this staff member. Locks the matching rows for update,
     * so callers MUST invoke this inside a DB::transaction() to actually
     * prevent race conditions between concurrent booking attempts.
     */
    public function isRangeAvailable(Staff $staff, string $date, string $startTime, string $endTime): bool
    {
        $overlapping = Appointment::query()
            ->where('staff_id', $staff->id)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->get();

        return $overlapping->isEmpty();
    }

    public function isDayOff(Staff $staff, string $date): bool
    {
        return $staff->daysOff()->whereDate('date', $date)->exists();
    }

    /**
     * Call after booking, cancelling, or changing a staff member's schedule/day off.
     */
    public function invalidate(Staff $staff, string $date): void
    {
        Cache::forget($this->cacheKey($staff, $date));
    }

    private function cacheKey(Staff $staff, string $date): string
    {
        return "slots:staff:{$staff->id}:{$date}";
    }
}
