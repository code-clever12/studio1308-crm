<?php

namespace App\Models;

use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'bio',
        'photo',
        'commission_rate',
        'status',
        'hire_date',
        'bank_account_routing_number',
        'bank_account_number',
        'bank_account_holder_name',
        'stripe_connect_account_id',
    ];

    protected $hidden = [
        'bank_account_routing_number',
        'bank_account_number',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'hire_date' => 'date',
            'bank_account_routing_number' => 'encrypted',
            'bank_account_number' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff')
            ->using(ServiceStaff::class)
            ->withPivot('is_available');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function daysOff(): HasMany
    {
        return $this->hasMany(DayOff::class);
    }

    public function achBankAccount(): HasOne
    {
        return $this->hasOne(ACHBankAccount::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function achPayouts(): HasMany
    {
        return $this->hasMany(ACHPayout::class);
    }

    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }
}
