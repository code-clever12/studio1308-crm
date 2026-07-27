<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form' => [
                'id' => $this->form->id,
                'name' => $this->form->name,
                'slug' => $this->form->slug,
            ],
            'payload' => $this->payload,
            'url' => $this->url,
            'utm_parameters' => $this->utm_parameters,
            'value' => $this->value !== null ? (float) $this->value : null,
            'service' => $this->service,
            'status' => $this->status,
            'capture_status' => $this->capture_status,
            'submission_time' => $this->submission_time?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
