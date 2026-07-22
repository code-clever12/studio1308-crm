<?php

namespace App\Models;

use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A lead-capture form definition (e.g. "Summer Offer 20%"), identified by
 * the slug an external landing page sends with each submission. Distinct
 * from ConsentForm, which is the in-booking-flow intake/consent form
 * builder — these two never share data or routes.
 */
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Finds the Form matching $slug, or creates one using $name (falling
     * back to a title-cased version of the slug if no name was given).
     */
    public static function findOrCreateBySlug(string $slug, ?string $name = null): self
    {
        return static::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name ?: Str::headline($slug)]
        );
    }
}
