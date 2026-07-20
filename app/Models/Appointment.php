<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'staff_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'service_price',
        'subtotal',
        'sales_tax_amount',
        'deposit_paid',
        'deposit_percentage',
        'total_amount',
        'remaining_balance',
        'cancellation_fee',
        'cancellation_reason',
        'notes',
        'payment_status',
        'stripe_payment_intent_id',
        'tip_amount',
        'no_show_fee_charged',
        'reminder_sent',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'service_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'sales_tax_amount' => 'decimal:2',
            'deposit_paid' => 'decimal:2',
            'deposit_percentage' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'cancellation_fee' => 'decimal:2',
            'tip_amount' => 'decimal:2',
            'no_show_fee_charged' => 'boolean',
            'reminder_sent' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function formResponses(): HasMany
    {
        return $this->hasMany(AppointmentFormResponse::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }

    public function salesTaxTransactions(): HasMany
    {
        return $this->hasMany(SalesTaxTransaction::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function waitlistConversion(): HasOne
    {
        return $this->hasOne(Waitlist::class, 'converted_appointment_id');
    }

    /**
     * "Add to Google Calendar" link for the confirmation page — no ICS file
     * generation needed since Google's render endpoint just takes query params.
     */
    public function calendarInviteUrl(): string
    {
        $start = Carbon::parse($this->appointment_date->toDateString().' '.$this->start_time);
        $end = Carbon::parse($this->appointment_date->toDateString().' '.$this->end_time);

        $params = [
            'action' => 'TEMPLATE',
            'text' => $this->service->name.' at '.config('app.name'),
            'dates' => $start->format('Ymd\THis').'/'.$end->format('Ymd\THis'),
            'details' => __('Your appointment at :salon.', ['salon' => config('app.name')]),
        ];

        return 'https://calendar.google.com/calendar/render?'.http_build_query($params);
    }
}
