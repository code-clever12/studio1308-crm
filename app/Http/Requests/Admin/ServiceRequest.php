<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'consent_form_id' => ['nullable', 'integer', 'exists:consent_forms,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
            'buffer_time_minutes' => ['nullable', 'integer', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'requires_consent_form' => ['boolean'],
            'is_taxable' => ['boolean'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
