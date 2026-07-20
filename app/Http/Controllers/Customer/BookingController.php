<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\BookingUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingRequest;
use App\Models\Service;
use App\Models\Staff;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly SlotService $slotService,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function create(Request $request): View
    {
        $services = Service::with(['category', 'consentForm', 'staff'])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(fn (Service $service) => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'category' => $service->category?->name,
                'price' => (float) $service->price,
                'duration_minutes' => $service->duration_minutes,
                'is_taxable' => $service->is_taxable,
                'requires_consent_form' => $service->requires_consent_form,
                'consent_form' => $service->consentForm ? [
                    'id' => $service->consentForm->id,
                    'name' => $service->consentForm->name,
                    'fields' => $service->consentForm->fields_json,
                ] : null,
            ]);

        $staff = Staff::with('user')
            ->where('status', 'active')
            ->withAvg('reviews', 'rating')
            ->get()
            ->map(fn (Staff $member) => [
                'id' => $member->id,
                'name' => $member->user->name,
                'bio' => $member->bio,
                'rating' => $member->reviews_avg_rating ? round((float) $member->reviews_avg_rating, 1) : null,
                'service_ids' => $member->services->pluck('id')->all(),
            ]);

        return view('customer.booking.create', [
            'servicesForJs' => $services,
            'staffForJs' => $staff,
            'preselectedServiceId' => $request->integer('service') ?: null,
        ]);
    }

    /**
     * AJAX endpoint: available time slots for a service/date, optionally for a specific staff member.
     */
    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $service = Service::findOrFail($data['service_id']);

        $staffList = isset($data['staff_id'])
            ? Staff::where('id', $data['staff_id'])->get()
            : $service->staff()->wherePivot('is_available', true)->where('status', 'active')->get();

        $slots = $staffList
            ->mapWithKeys(fn (Staff $staff) => [$staff->id => $this->slotService->getAvailableSlots($staff, $data['date'], $service)])
            ->filter(fn (array $staffSlots) => ! empty($staffSlots));

        return response()->json(['slots' => $slots]);
    }

    /**
     * AJAX endpoint: price breakdown (subtotal, tax, deposit, tip options, total) for checkout.
     */
    public function breakdown(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'tip' => ['nullable', 'numeric', 'min:0'],
        ]);

        $service = Service::findOrFail($data['service_id']);

        return response()->json($this->paymentService->calculateBreakdown($service, (float) ($data['tip'] ?? 0)));
    }

    public function store(BookingRequest $request): RedirectResponse|JsonResponse
    {
        $staff = $request->validated('staff_id') ? Staff::findOrFail($request->validated('staff_id')) : null;

        try {
            $appointment = $this->bookingService->book(
                $request->user(),
                Service::findOrFail($request->validated('service_id')),
                $staff,
                $request->validated('appointment_date'),
                $request->validated('start_time'),
                $request->validated('form_responses', []),
            );
        } catch (BookingUnavailableException $e) {
            return $this->bookingErrorResponse($request, 'start_time', $e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->bookingErrorResponse($request, 'booking', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['appointment_id' => $appointment->id], 201);
        }

        return redirect()->route('customer.appointments.index')
            ->with('status', 'Appointment requested! We will confirm shortly.')
            ->with('appointment_id', $appointment->id);
    }

    /**
     * Join the waitlist when no slot is available for a preferred date/staff.
     */
    public function joinWaitlist(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'time_preference' => ['nullable', 'date_format:H:i'],
        ]);

        $this->bookingService->joinWaitlist(
            $request->user(),
            Service::findOrFail($data['service_id']),
            isset($data['staff_id']) ? Staff::findOrFail($data['staff_id']) : null,
            $data['requested_date'],
            $data['time_preference'] ?? null,
        );

        if ($request->expectsJson()) {
            return response()->json(['joined' => true], 201);
        }

        return redirect()->route('dashboard')->with('status', "You're on the waitlist! We'll notify you if a spot opens up.");
    }

    private function bookingErrorResponse(Request $request, string $field, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }
}
