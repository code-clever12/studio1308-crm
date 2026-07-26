<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitFormRequest;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

/**
 * Public, unauthenticated endpoint for external static landing pages
 * (e.g. GitHub Pages) to post lead-capture submissions — see
 * routes/api.php for the route/throttle and config/cors.php for which
 * origins may call it cross-origin.
 */
class FormSubmissionController extends Controller
{
    /**
     * Fields this app reads by name; everything else in the request is
     * dynamic per-landing-page form data and goes into payload as-is.
     */
    private const META_KEYS = ['form_slug', 'form_name', 'url', 'value', 'draft_id', 'is_partial'];

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function store(SubmitFormRequest $request): JsonResponse
    {
        $form = Form::findOrCreateBySlug(
            $request->string('form_slug')->toString(),
            $request->string('form_name')->toString() ?: null,
        );

        $utmParameters = collect($request->all())
            ->filter(fn ($value, string $key) => str_starts_with($key, 'utm_'))
            ->all();

        $payload = collect($request->all())
            ->reject(fn ($value, string $key) => in_array($key, self::META_KEYS, true) || str_starts_with($key, 'utm_'))
            ->all();

        $draftId = $request->string('draft_id')->toString() ?: null;
        $isPartial = $request->boolean('is_partial');

        // Re-submitting the same draft_id (e.g. autosave as the visitor
        // fills the form) updates the existing row instead of creating a
        // duplicate. A brand-new submission (no draft_id, or a draft_id not
        // seen before) creates a fresh row either way.
        $submission = $draftId !== null
            ? FormSubmission::firstOrNew(['draft_id' => $draftId])
            : new FormSubmission;

        $isNewRecord = ! $submission->exists;

        $submission->fill([
            'form_id' => $form->id,
            'draft_id' => $draftId,
            'payload' => $payload,
            'url' => $request->string('url')->toString() ?: null,
            'utm_parameters' => $utmParameters ?: null,
            'value' => $request->input('value'),
            'capture_status' => $isPartial ? 'abandoned' : 'completed',
            'submission_time' => now(),
        ]);

        // Lead-quality (status) is admin-managed follow-up tracking, not
        // part of this capture-state update — only set a default the first
        // time this row is created, never overwrite an admin's existing
        // classification on a later autosave/final submit of the same draft.
        if ($isNewRecord) {
            $submission->status = 'cold_lead';
        }

        $submission->save();

        // A partial (still-in-progress) capture isn't a lead yet — only
        // alert admins once the visitor actually finishes the form.
        if (! $isPartial) {
            $this->notificationService->sendNewFormSubmissionAlert($submission);
        }

        return response()->json([
            'success' => true,
            'id' => $submission->id,
        ], 201);
    }
}
