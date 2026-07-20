<?php

namespace App\Models;

use Database\Factories\DayOffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayOff extends Model
{
    /** @use HasFactory<DayOffFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'days_off';

    protected $fillable = [
        'staff_id',
        'date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
