<?php

namespace App\Models;

use Database\Factories\WaitlistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    /** @use HasFactory<WaitlistFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'staff_id',
        'service_id',
        'requested_date',
        'time_preference',
        'status',
        'notification_sent_at',
        'converted_appointment_id',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'notification_sent_at' => 'datetime',
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

    public function convertedAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'converted_appointment_id');
    }
}
