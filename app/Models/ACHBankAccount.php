<?php

namespace App\Models;

use Database\Factories\ACHBankAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ACHBankAccount extends Model
{
    /** @use HasFactory<ACHBankAccountFactory> */
    use HasFactory;

    protected $table = 'ach_bank_accounts';

    protected $fillable = [
        'staff_id',
        'bank_account_routing_number',
        'bank_account_number',
        'bank_account_holder_name',
        'bank_name',
        'verification_status',
        'stripe_bank_account_token',
        'last_4_digits',
    ];

    protected $hidden = [
        'bank_account_routing_number',
        'bank_account_number',
    ];

    protected function casts(): array
    {
        return [
            'bank_account_routing_number' => 'encrypted',
            'bank_account_number' => 'encrypted',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
