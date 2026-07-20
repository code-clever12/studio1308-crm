<?php

namespace App\Models;

use Database\Factories\SalesTaxTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTaxTransaction extends Model
{
    /** @use HasFactory<SalesTaxTransactionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'state',
        'tax_rate',
        'taxable_amount',
        'tax_amount',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
