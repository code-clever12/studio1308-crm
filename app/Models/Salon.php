<?php

namespace App\Models;

use Database\Factories\SalonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Single-location salon configuration. This platform is single-tenant, so
 * Staff/Service/Appointment/Payment records have no salon_id column — they
 * all implicitly belong to this one salon row. The relationship helpers
 * below reflect that: they return query builders rather than FK-based
 * Eloquent relations.
 */
class Salon extends Model
{
    /** @use HasFactory<SalonFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'lead_notification_emails',
        'website',
        'logo',
        'timezone',
        'opens_at',
        'closes_at',
        'cancellation_policy',
        'deposit_percentage',
        'no_show_fee',
        'enable_tips',
        'sales_tax_rate',
        'acct_stripe_connect_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime:H:i',
            'closes_at' => 'datetime:H:i',
            'deposit_percentage' => 'decimal:2',
            'no_show_fee' => 'decimal:2',
            'enable_tips' => 'boolean',
            'sales_tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Extra addresses (beyond admin-role users) that should receive lead
     * notification emails — see NotificationService::sendNewFormSubmissionAlert().
     * Stored as a single comma-separated string so the Settings page can use
     * a plain textarea rather than a repeatable field builder.
     *
     * @return array<int, string>
     */
    public function leadNotificationEmails(): array
    {
        return collect(explode(',', (string) $this->lead_notification_emails))
            ->map(fn (string $email) => trim($email))
            ->filter()
            ->values()
            ->all();
    }

    public function consentForms(): HasMany
    {
        return $this->hasMany(ConsentForm::class);
    }

    /**
     * All staff for this (single) salon. No FK — every Staff row belongs here.
     */
    public function staff()
    {
        return Staff::query();
    }

    /**
     * All services for this (single) salon. No FK — every Service row belongs here.
     */
    public function services()
    {
        return Service::query();
    }

    /**
     * All appointments for this (single) salon. No FK — every Appointment row belongs here.
     */
    public function appointments()
    {
        return Appointment::query();
    }

    /**
     * All payments for this (single) salon. No FK — every Payment row belongs here.
     */
    public function payments()
    {
        return Payment::query();
    }
}
