<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\Staff;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(private readonly SlotService $slotService)
    {
    }

    public function edit(Staff $staff): View
    {
        $this->authorize('update', $staff);

        return view('admin.schedules.edit', [
            'staff' => $staff->load('schedules', 'daysOff'),
        ]);
    }

    public function update(ScheduleRequest $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);

        // Normalize "H:i" input to "H:i:s" — MySQL silently pads a bare TIME
        // value on write, but SQLite (used in tests) stores it verbatim.
        $normalizeTime = fn (?string $time) => $time ? Carbon::parse($time)->format('H:i:s') : null;

        foreach ($request->validated('schedules') as $day) {
            $staff->schedules()->updateOrCreate(
                ['day_of_week' => $day['day_of_week']],
                [
                    'is_working_day' => $day['is_working_day'] ?? false,
                    'start_time' => $normalizeTime($day['start_time'] ?? null),
                    'end_time' => $normalizeTime($day['end_time'] ?? null),
                    'break_start' => $normalizeTime($day['break_start'] ?? null),
                    'break_end' => $normalizeTime($day['break_end'] ?? null),
                ]
            );
        }

        return redirect()->route('admin.schedules.edit', $staff)->with('status', 'Schedule updated.');
    }

    public function storeDayOff(Request $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $staff->daysOff()->create($data);

        $this->slotService->invalidate($staff, $data['date']);

        return redirect()->route('admin.schedules.edit', $staff)->with('status', 'Day off added.');
    }

    public function destroyDayOff(Staff $staff, int $dayOff): RedirectResponse
    {
        $this->authorize('update', $staff);

        $entry = $staff->daysOff()->findOrFail($dayOff);
        $date = $entry->date->toDateString();
        $entry->delete();

        $this->slotService->invalidate($staff, $date);

        return redirect()->route('admin.schedules.edit', $staff)->with('status', 'Day off removed.');
    }
}
