<?php

namespace App\Models;

use Database\Factories\ACHPayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ACHPayout extends Model
{
    /** @use HasFactory<ACHPayoutFactory> */
    use HasFactory;

    protected $table = 'ach_payouts';

    protected $fillable = [
        'staff_id',
        'amount',
        'status',
        'stripe_payout_id',
        'payout_date',
        'expected_arrival_date',
        'failure_reason',
        'commission_amount',
        'tips_amount',
        'adjustments_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payout_date' => 'date',
            'expected_arrival_date' => 'date',
            'commission_amount' => 'decimal:2',
            'tips_amount' => 'decimal:2',
            'adjustments_amount' => 'decimal:2',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
