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
    private const META_KEYS = ['form_slug', 'form_name', 'url', 'value'];

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

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'payload' => $payload,
            'url' => $request->string('url')->toString() ?: null,
            'utm_parameters' => $utmParameters ?: null,
            'value' => $request->input('value'),
            'status' => 'new',
            'submission_time' => now(),
        ]);

        $this->notificationService->sendNewFormSubmissionAlert($submission);

        return response()->json([
            'success' => true,
            'id' => $submission->id,
        ], 201);
    }
}
