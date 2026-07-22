<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only view of leads captured via POST /api/v1/submit-form (see
 * Api\FormSubmissionController) — authorization is handled by the
 * 'role:admin' route middleware, no dedicated Policy, matching how
 * Admin\CustomerController handles a similarly simple read/update model.
 */
class FormSubmissionController extends Controller
{
    public function index(): View
    {
        return view('admin.form-submissions.index', [
            // whereHas (an EXISTS subquery) rather than having() on the
            // withCount alias — HAVING on a subquery-derived column is
            // accepted by MySQL but not SQLite, which the test suite runs on.
            'forms' => Form::withCount('submissions')
                ->whereHas('submissions')
                ->orderByDesc('submissions_count')
                ->get(),
        ]);
    }

    public function show(Form $form): View
    {
        $submissions = $form->submissions()->latest('submission_time')->get();

        return view('admin.form-submissions.show', [
            'form' => $form,
            'submissions' => $submissions,
            // Union of payload keys across this form's submissions, so the
            // table gets one column per field rather than a raw JSON dump —
            // safe to assume a consistent shape per form since one form
            // normally maps to one landing page's field set.
            'payloadKeys' => $submissions->flatMap(fn (FormSubmission $s) => array_keys($s->payload ?? []))->unique()->values(),
        ]);
    }

    public function updateStatus(Request $request, FormSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,converted,archived'],
        ]);

        $submission->update($data);

        return back()->with('status', 'Submission status updated.');
    }
}
