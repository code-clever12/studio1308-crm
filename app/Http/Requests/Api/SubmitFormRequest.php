<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Public endpoint — external static landing pages (no Laravel session)
 * submit here, so this is always authorized; safety is rate limiting
 * (see routes/api.php) plus the validation below, not auth.
 */
class SubmitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Only the fields this app actually reads by name are validated here.
     * Everything else in the request (full_name, phone_number, hair_type,
     * or whatever a given landing page collects) is dynamic by design and
     * gets captured as-is into FormSubmission.payload — see
     * Api\FormSubmissionController::store().
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'form_slug' => ['required', 'string', 'max:255'],
            'form_name' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'draft_id' => ['nullable', 'string', 'max:255'],
            'is_partial' => ['nullable', 'boolean'],
        ];
    }
}
