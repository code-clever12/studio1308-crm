<?php

namespace App\Models;

use Database\Factories\ConsentFormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentForm extends Model
{
    /** @use HasFactory<ConsentFormFactory> */
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'name',
        'description',
        'fields_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fields_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointmentFormResponses(): HasMany
    {
        return $this->hasMany(AppointmentFormResponse::class);
    }
}
