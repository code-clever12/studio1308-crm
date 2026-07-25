<?php

namespace App\Http\Requests\Admin;

use Closure;
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
            'lead_notification_emails' => ['nullable', 'string', $this->validEmailList(...)],
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

    /**
     * Validates a comma-separated list of email addresses, e.g. the value
     * submitted for 'lead_notification_emails'.
     */
    protected function validEmailList(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (explode(',', (string) $value) as $email) {
            $email = trim($email);

            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fail(__(':email is not a valid email address.', ['email' => $email]));

                return;
            }
        }
    }
}
