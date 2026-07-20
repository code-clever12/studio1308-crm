<?php

namespace App\Models;

use Database\Factories\LoyaltyPointsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyPoints extends Model
{
    /** @use HasFactory<LoyaltyPointsFactory> */
    use HasFactory;

    protected $table = 'loyalty_points';

    protected $fillable = [
        'customer_id',
        'balance',
        'earned_total',
        'redeemed_total',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'earned_total' => 'integer',
            'redeemed_total' => 'integer',
            'last_activity_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }
}
