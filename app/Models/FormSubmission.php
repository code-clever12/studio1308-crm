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

    /**
     * Independent of STATUSES (lead-quality, admin-managed): whether the
     * visitor finished the form (completed) or left it mid-way (abandoned),
     * driven by the is_partial flag on POST /api/v1/submit-form.
     */
    public const CAPTURE_STATUSES = ['abandoned', 'completed'];

    protected $fillable = [
        'form_id',
        'draft_id',
        'payload',
        'url',
        'utm_parameters',
        'value',
        'status',
        'capture_status',
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
