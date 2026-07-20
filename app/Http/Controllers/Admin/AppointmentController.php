<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CancellationService;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly CancellationService $cancellationService,
        private readonly SlotService $slotService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $date = $request->date('date') ?? today();

        $appointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->whereDate('appointment_date', $date)
            ->orderBy('start_time')
            ->get();

        return view('admin.appointments.index', compact('appointments', 'date'));
    }

    public function create(): View
    {
        $this->authorize('create', Appointment::class);

        return view('admin.appointments.create', [
            'customers' => User::where('role', 'customer')->orderBy('name')->get(),
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'staff' => Staff::with('user')->where('status', 'active')->get(),
        ]);
    }

    /**
     * Manual walk-in booking. Unlike online self-service bookings, walk-ins
     * are confirmed immediately since the front desk handles payment in person.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
        ]);

        $customer = User::findOrFail($data['customer_id']);
        $service = Service::findOrFail($data['service_id']);
        $staff = isset($data['staff_id']) ? Staff::findOrFail($data['staff_id']) : null;

        $appointment = $this->bookingService->book(
            $customer,
            $service,
            $staff,
            $data['appointment_date'],
            $data['start_time'],
        );

        $appointment->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'deposit_paid' => $appointment->total_amount,
            'remaining_balance' => 0,
        ]);

        return redirect()->route('admin.appointments.index')->with('status', 'Appointment booked.');
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
        ]);

        if (! $appointment->staff) {
            abort(422, 'Cannot reschedule an appointment with no assigned staff.');
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

        return redirect()->route('admin.appointments.index')->with('status', 'Appointment rescheduled.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $this->cancellationService->cancel($appointment, $request->input('reason'));

        return redirect()->route('admin.appointments.index')->with('status', 'Appointment cancelled.');
    }
}
