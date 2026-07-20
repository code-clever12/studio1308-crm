<?php

namespace App\Models;

use Database\Factories\LoyaltyTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    /** @use HasFactory<LoyaltyTransactionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'loyalty_points_id',
        'appointment_id',
        'type',
        'points',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function loyaltyPoints(): BelongsTo
    {
        return $this->belongsTo(LoyaltyPoints::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
