<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'website' => ['nullable', 'url'],
            'timezone' => ['required', 'string'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i', 'after:opens_at'],
            'cancellation_policy' => ['nullable', 'string'],
            'deposit_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'no_show_fee' => ['required', 'numeric', 'min:0'],
            'enable_tips' => ['boolean'],
            'sales_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
