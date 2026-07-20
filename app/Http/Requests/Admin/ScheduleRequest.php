<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ScheduleRequest extends FormRequest
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
            'schedules' => ['required', 'array', 'size:7'],
            'schedules.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'schedules.*.is_working_day' => ['boolean'],
            'schedules.*.start_time' => ['nullable', 'date_format:H:i'],
            'schedules.*.end_time' => ['nullable', 'date_format:H:i'],
            'schedules.*.break_start' => ['nullable', 'date_format:H:i'],
            'schedules.*.break_end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('schedules', []) as $index => $day) {
                if (! ($day['is_working_day'] ?? false)) {
                    continue;
                }

                if (empty($day['start_time']) || empty($day['end_time'])) {
                    $validator->errors()->add("schedules.{$index}.start_time", 'Start and end time are required for a working day.');

                    continue;
                }

                if ($day['start_time'] >= $day['end_time']) {
                    $validator->errors()->add("schedules.{$index}.end_time", 'End time must be after start time.');
                }

                if (! empty($day['break_start']) && ! empty($day['break_end']) && $day['break_start'] >= $day['break_end']) {
                    $validator->errors()->add("schedules.{$index}.break_end", 'Break end must be after break start.');
                }
            }
        });
    }
}
