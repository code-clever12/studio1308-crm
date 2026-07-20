<?php

namespace App\Models;

use Database\Factories\AppointmentFormResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentFormResponse extends Model
{
    /** @use HasFactory<AppointmentFormResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'consent_form_id',
        'form_data_json',
    ];

    protected function casts(): array
    {
        return [
            'form_data_json' => 'array',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function consentForm(): BelongsTo
    {
        return $this->belongsTo(ConsentForm::class);
    }
}
