<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'services_json',
        'total_duration_minutes',
        'discount_percentage',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'services_json' => 'array',
            'total_duration_minutes' => 'integer',
            'discount_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The services bundled in this package, resolved from services_json.
     * Not a true FK relation — service IDs are stored as a JSON array per the spec.
     */
    public function services(): Collection
    {
        return Service::whereIn('id', $this->services_json ?? [])->get();
    }
}
