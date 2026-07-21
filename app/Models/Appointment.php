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

    /**
     * Raw iCalendar (.ics) file content for the booking confirmation email
     * attachment. Uses floating local time (no UTC/TZID conversion) since
     * the rest of the app treats appointment_date/start_time/end_time as
     * plain salon-local wall-clock values with no timezone handling —
     * matching that here avoids a mismatch with what customers see on-screen.
     */
    public function toIcsContent(): string
    {
        $start = Carbon::parse($this->appointment_date->toDateString().' '.$this->start_time);
        $end = Carbon::parse($this->appointment_date->toDateString().' '.$this->end_time);
        $salon = Salon::query()->first();

        $location = $salon
            ? trim("{$salon->address}, {$salon->city}, {$salon->state} {$salon->zip_code}", ' ,')
            : '';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.config('app.name').'//Booking//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:appointment-'.$this->id.'@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'ritualbarberstudio.test'),
            'DTSTAMP:'.now()->format('Ymd\THis'),
            'DTSTART:'.$start->format('Ymd\THis'),
            'DTEND:'.$end->format('Ymd\THis'),
            'SUMMARY:'.$this->icsEscape($this->service->name.' at '.config('app.name')),
            'DESCRIPTION:'.$this->icsEscape('Appointment for '.$this->service->name.($this->staff ? ' with '.$this->staff->user->name : '')),
            'LOCATION:'.$this->icsEscape($location),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    private function icsEscape(string $value): string
    {
        return str_replace(['\\', ',', ';', "\n"], ['\\\\', '\\,', '\\;', '\\n'], $value);
    }
}
