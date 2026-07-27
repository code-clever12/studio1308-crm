<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormSubmissionResource;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Authenticated (Sanctum) admin-only JSON API for the mobile app — leads
 * only, mirrors Admin\FormSubmissionController's web equivalent but returns
 * JSON instead of Blade views. Authorization is the 'role:admin' route
 * middleware (see routes/api.php), same as the web admin panel.
 */
class SubmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $submissions = $this->filtered($request)->paginate(20)->withQueryString();

        return FormSubmissionResource::collection($submissions)->response();
    }

    public function show(FormSubmission $submission): JsonResponse
    {
        return response()->json([
            'data' => new FormSubmissionResource($submission->load('form')),
        ]);
    }

    public function update(Request $request, FormSubmission $submission): JsonResponse
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

        return response()->json([
            'data' => new FormSubmissionResource($submission->fresh('form')),
        ]);
    }

    public function updateStatus(Request $request, FormSubmission $submission): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', FormSubmission::STATUSES)],
        ]);

        $submission->update($data);

        return response()->json([
            'data' => new FormSubmissionResource($submission->fresh('form')),
        ]);
    }

    public function destroy(FormSubmission $submission): JsonResponse
    {
        $submission->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Streams a CSV of every submission matching the current filters — not
     * paginated, since an export is expected to cover the whole filtered set.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request);
        $filename = 'submissions-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Form', 'Payload', 'URL', 'Value', 'Service',
                'Capture Status', 'Lead Status', 'Submitted At',
            ]);

            $query->chunk(200, function ($submissions) use ($handle) {
                foreach ($submissions as $submission) {
                    fputcsv($handle, [
                        $submission->id,
                        $submission->form->name,
                        json_encode($submission->payload),
                        $submission->url,
                        $submission->value,
                        $submission->service,
                        $submission->capture_status,
                        $submission->status,
                        $submission->submission_time->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function services(): JsonResponse
    {
        return response()->json([
            'data' => FormSubmission::query()->whereNotNull('service')->distinct()->orderBy('service')->pluck('service'),
        ]);
    }

    private function filtered(Request $request): Builder
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

        return $query;
    }
}
