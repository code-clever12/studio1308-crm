<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('view', $appointment);

        abort_unless($appointment->status === 'completed', 422, 'Only completed appointments can be reviewed.');
        abort_if($appointment->review()->exists(), 422, 'This appointment has already been reviewed.');
        abort_unless($appointment->staff_id, 422, 'This appointment has no staff member to review.');

        Review::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'staff_id' => $appointment->staff_id,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'is_verified_booking' => true,
        ]);

        return redirect()->route('customer.appointments.index')->with('status', 'Thanks for your review!');
    }
}
