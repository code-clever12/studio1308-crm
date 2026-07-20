<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'coupon_id',
        'amount',
        'breakdown_json',
        'payment_method',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'currency',
        'status',
        'refund_amount',
        'refund_reason',
        'refund_date',
        'refund_stripe_id',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'breakdown_json' => 'array',
            'refund_amount' => 'decimal:2',
            'refund_date' => 'datetime',
            'payment_date' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
