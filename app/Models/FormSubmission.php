<?php

namespace App\Models;

use Database\Factories\FormSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    /** @use HasFactory<FormSubmissionFactory> */
    use HasFactory;

    public const STATUSES = ['cold_lead', 'warm_lead', 'hot_lead'];

    protected $fillable = [
        'form_id',
        'payload',
        'url',
        'utm_parameters',
        'value',
        'status',
        'submission_time',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'utm_parameters' => 'array',
            'value' => 'decimal:2',
            'submission_time' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
