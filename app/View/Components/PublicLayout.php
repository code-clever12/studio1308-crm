<?php

namespace App\View\Components;

use App\Models\Salon;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Shared shell for public/unauthenticated pages (homepage, and any future
 * public marketing pages) — head/meta/SEO tags, header nav, and footer live
 * here once instead of being duplicated per page. Deliberately NOT used by
 * the admin or customer/auth layouts, which have their own shells and no
 * SEO surface (behind login, never indexed).
 */
class PublicLayout extends Component
{
    public ?Salon $salon;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?float $averageRating = null,
        public ?int $reviewCount = null,
    ) {
        $this->salon = Salon::query()->first();
        $this->description ??= $this->salon?->description
            ?? 'Book your next appointment online — real-time availability, secure card payments, and a confirmation you can trust.';
    }

    public function render(): View
    {
        return view('layouts.public', [
            'siteName' => $this->salon?->name ?? config('app.name', '1308Studio'),
            'structuredData' => $this->structuredData(),
        ]);
    }

    /**
     * LocalBusiness/HairSalon JSON-LD — real NAP + hours + rating data so
     * search engines can show rich results (map pin, hours, star rating)
     * without needing a manual Google Business Profile sync.
     *
     * @return array<string, mixed>|null
     */
    private function structuredData(): ?array
    {
        if (! $this->salon) {
            return null;
        }

        $data = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'HairSalon',
            'name' => $this->salon->name,
            'description' => $this->salon->description,
            'telephone' => $this->salon->phone,
            'url' => url('/'),
        ]);

        if ($this->salon->address) {
            $data['address'] = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $this->salon->address,
                'addressLocality' => $this->salon->city,
                'addressRegion' => $this->salon->state,
                'postalCode' => $this->salon->zip_code,
                'addressCountry' => 'US',
            ]);
        }

        if ($this->salon->opens_at && $this->salon->closes_at) {
            $data['openingHoursSpecification'] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'opens' => $this->salon->opens_at->format('H:i'),
                'closes' => $this->salon->closes_at->format('H:i'),
            ];
        }

        if ($this->reviewCount) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($this->averageRating ?? 0, 1),
                'reviewCount' => $this->reviewCount,
            ];
        }

        return $data;
    }
}
