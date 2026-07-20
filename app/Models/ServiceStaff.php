<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceStaff extends Pivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'service_staff';

    protected $fillable = [
        'service_id',
        'staff_id',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
