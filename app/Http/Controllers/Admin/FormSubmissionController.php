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

    public function all(Request $request): View
    {
        $query = FormSubmission::with('form')->latest('submission_time');

        if ($request->filled('form_id')) {
            $query->where('form_id', $request->integer('form_id'));
        }

        if ($request->filled('service')) {
            $query->where('service', $request->string('service')->toString());
        }

        if ($request->filled('capture_status')) {
            $query->where('capture_status', $request->string('capture_status')->toString());
        }

        $submissions = $query->paginate(20)->withQueryString();

        return view('admin.form-submissions.all', [
            'submissions' => $submissions,
            'payloadKeys' => $submissions->flatMap(fn (FormSubmission $s) => array_keys($s->payload ?? []))->unique()->values(),
            'forms' => Form::orderBy('name')->get(),
            // Now a plain column query instead of scanning every payload's
            // JSON for a 'service' key — simpler and faster now that
            // Api\FormSubmissionController::store() writes it as a real column.
            'services' => FormSubmission::query()->whereNotNull('service')->distinct()->orderBy('service')->pluck('service'),
            'selectedFormId' => $request->integer('form_id') ?: null,
            'selectedService' => $request->string('service')->toString() ?: null,
            'selectedCaptureStatus' => $request->string('capture_status')->toString() ?: null,
        ]);
    }

    public function chatLeads(): View
    {
        $submissions = FormSubmission::with('form')->latest('submission_time')->paginate(20);

        return view('admin.form-submissions.chat-leads', [
            'submissions' => $submissions,
            'payloadKeys' => $submissions->flatMap(fn (FormSubmission $s) => array_keys($s->payload ?? []))->unique()->values(),
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
            'status' => ['required', 'in:'.implode(',', FormSubmission::STATUSES)],
        ]);

        $submission->update($data);

        return back()->with('status', 'Submission status updated.');
    }

    public function edit(FormSubmission $submission): View
    {
        return view('admin.form-submissions.edit', [
            'submission' => $submission,
        ]);
    }

    public function update(Request $request, FormSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'payload' => ['nullable', 'array'],
            'payload.*' => ['nullable', 'string', 'max:2000'],
            'url' => ['nullable', 'string', 'max:2048'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', FormSubmission::STATUSES)],
        ]);

        $submission->update([
            'payload' => $data['payload'] ?? [],
            'url' => $data['url'] ?? null,
            'value' => $data['value'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('admin.form-submissions.show', $submission->form)
            ->with('status', 'Submission updated.');
    }

    public function destroy(FormSubmission $submission): RedirectResponse
    {
        $form = $submission->form;
        $submission->delete();

        return redirect()->route('admin.form-submissions.show', $form)
            ->with('status', 'Submission deleted.');
    }
}
