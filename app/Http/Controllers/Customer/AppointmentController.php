<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\CancellationService;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly CancellationService $cancellationService,
        private readonly SlotService $slotService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $appointments = $request->user()->appointments()
            ->with(['service', 'staff.user'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->get()
            ->groupBy(fn (Appointment $appointment) => match (true) {
                $appointment->status === 'cancelled' => 'cancelled',
                in_array($appointment->status, ['completed', 'no_show']) => 'past',
                default => 'upcoming',
            });

        return view('customer.appointments', [
            'upcoming' => $appointments->get('upcoming', collect()),
            'past' => $appointments->get('past', collect()),
            'cancelled' => $appointments->get('cancelled', collect()),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
        ]);

        if (! $appointment->staff) {
            return back()->withErrors(['start_time' => 'This appointment has no assigned staff member to reschedule with.']);
        }

        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = Carbon::parse($startTime)
            ->addMinutes($appointment->service->duration_minutes + $appointment->service->buffer_time_minutes)
            ->format('H:i:s');

        if (! $this->slotService->isRangeAvailable($appointment->staff, $data['appointment_date'], $startTime, $endTime)) {
            return back()->withErrors(['start_time' => 'This time slot is not available.']);
        }

        $oldDate = $appointment->appointment_date->toDateString();

        $appointment->update([
            'appointment_date' => $data['appointment_date'],
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $this->slotService->invalidate($appointment->staff, $oldDate);
        $this->slotService->invalidate($appointment->staff, $data['appointment_date']);

        return redirect()->route('customer.appointments.index')->with('status', 'Appointment rescheduled.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $this->cancellationService->cancel($appointment, $request->input('reason'));

        return redirect()->route('customer.appointments.index')->with('status', 'Appointment cancelled.');
    }
}
