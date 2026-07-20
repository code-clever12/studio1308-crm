<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConsentFormRequest extends FormRequest
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
            'is_active' => ['boolean'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.id' => ['required', 'string'],
            'fields.*.label' => ['required', 'string'],
            'fields.*.type' => ['required', 'string', 'in:text,email,checkbox,radio,date,file'],
            'fields.*.required' => ['boolean'],
            // Submitted as a comma-separated string from the form-builder component;
            // FormBuilderController splits it into an array before persisting.
            'fields.*.options' => ['nullable', 'string'],
        ];
    }
}
