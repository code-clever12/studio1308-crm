<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', '1308Studio') }}</title>
        <meta name="description" content="{{ $salon?->description ?? 'Book your next appointment online — real-time availability, secure card payments, and a confirmation you can trust.' }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|oswald:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-parchment text-ink">
        <header class="bg-pine-950 text-parchment">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
                <span class="font-display text-xl tracking-wide">
                    {{ $salon?->name ?? config('app.name', '1308Studio') }}
                </span>

                <nav class="flex items-center gap-3 sm:gap-5">
                    @if ($salon?->phone)
                        <a href="tel:{{ $salon->phone }}" class="hidden sm:inline text-sm font-medium text-parchment/80 hover:text-parchment">
                            {{ $salon->phone }}
                        </a>
                    @endif
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-parchment/80 hover:text-parchment">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-parchment/80 hover:text-parchment">
                            {{ __('Log in') }}
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brass-500 text-pine-950 text-sm font-medium hover:bg-brass-400 transition-colors">
                            {{ __('Book Now') }}
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <section class="bg-pine-950 text-parchment">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 pt-6 sm:pt-10">
                <p class="font-display text-sm uppercase tracking-widest text-brass-400 mb-3">
                    {{ $salon?->city && $salon?->state ? "{$salon->city}, {$salon->state}" : __('Book online') }}
                </p>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl leading-[1.1] max-w-3xl">
                    {{ __('Look your best. Book it yourself.') }}
                </h1>
                <p class="mt-5 text-parchment/70 text-lg max-w-xl">
                    {{ $salon?->description ?? __('Real-time availability, secure deposit payments, and a confirmation the moment you book — no phone calls required.') }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex items-center px-6 py-3 rounded-md bg-brass-500 text-pine-950 font-medium hover:bg-brass-400 transition-colors">
                        {{ __('Book an Appointment') }}
                    </a>
                    @if ($categories->isNotEmpty())
                        <a href="#services" class="text-sm font-medium text-parchment/80 hover:text-parchment underline underline-offset-4">
                            {{ __('See services & pricing') }}
                        </a>
                    @elseif (! auth()->check())
                        <a href="{{ route('login') }}" class="text-sm font-medium text-parchment/80 hover:text-parchment underline underline-offset-4">
                            {{ __('Already have an account? Log in') }}
                        </a>
                    @endif
                </div>

                @if ($reviewCount > 0 || $salon?->address)
                    <div class="mt-8 pt-6 border-t border-parchment/10 flex flex-wrap gap-x-8 gap-y-2 text-sm text-parchment/70">
                        @if ($reviewCount > 0)
                            <span><span class="text-brass-400">★</span> {{ number_format($averageRating, 1) }} · {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                        @endif
                        @if ($salon?->address)
                            <span>{{ trim("{$salon->address}, {$salon->city}, {$salon->state} {$salon->zip_code}", ' ,') }}</span>
                        @endif
                        @if ($salon?->opens_at && $salon?->closes_at)
                            <span>{{ $salon->opens_at->format('g:i A') }} – {{ $salon->closes_at->format('g:i A') }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        @if ($categories->isNotEmpty())
            <section id="services" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <p class="font-display text-sm uppercase tracking-widest text-brass-600 mb-2">{{ __('Services & pricing') }}</p>
                <h2 class="font-display text-2xl sm:text-3xl text-ink mb-10">{{ __('Book exactly what you came for.') }}</h2>

                <div class="space-y-10">
                    @foreach ($categories as $category)
                        <div>
                            <h3 class="font-display text-lg text-pine-800 mb-4">{{ $category->name }}</h3>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($category->services as $service)
                                    <x-service-card :service="$service">
                                        <x-slot:actions>
                                            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn-secondary w-full">
                                                {{ __('Book This') }}
                                            </a>
                                        </x-slot:actions>
                                    </x-service-card>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <p class="font-display text-sm uppercase tracking-widest text-brass-600 mb-2">{{ __('How it works') }}</p>
            <h2 class="font-display text-2xl sm:text-3xl text-ink mb-10">{{ __('Three steps, no waiting on hold.') }}</h2>

            <div class="grid sm:grid-cols-3 gap-6">
                <div class="card p-6">
                    <p class="font-display text-3xl text-brass-500 mb-3">01</p>
                    <h3 class="font-display text-lg text-ink mb-2">{{ __('Pick your service & stylist') }}</h3>
                    <p class="text-sm text-ink/60">{{ __('Browse services and staff, or let us match you with the first available.') }}</p>
                </div>
                <div class="card p-6">
                    <p class="font-display text-3xl text-brass-500 mb-3">02</p>
                    <h3 class="font-display text-lg text-ink mb-2">{{ __('See real availability') }}</h3>
                    <p class="text-sm text-ink/60">{{ __('Every open slot accounts for actual schedules and existing bookings — what you see is what you get.') }}</p>
                </div>
                <div class="card p-6">
                    <p class="font-display text-3xl text-brass-500 mb-3">03</p>
                    <h3 class="font-display text-lg text-ink mb-2">{{ __('Confirm with a deposit') }}</h3>
                    <p class="text-sm text-ink/60">{{ __("Secure your slot with a card deposit, add a tip if you'd like, and get an emailed confirmation with a calendar invite.") }}</p>
                </div>
            </div>
        </section>

        @if ($team->isNotEmpty())
            <section class="bg-parchment-deep">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <p class="font-display text-sm uppercase tracking-widest text-brass-600 mb-2">{{ __('Meet the team') }}</p>
                    <h2 class="font-display text-2xl sm:text-3xl text-ink mb-10">{{ __('Licensed stylists, hand-picked.') }}</h2>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($team as $staff)
                            <x-staff-card :staff="$staff" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($reviews->isNotEmpty())
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <p class="font-display text-sm uppercase tracking-widest text-brass-600 mb-2">{{ __('What clients say') }}</p>
                <h2 class="font-display text-2xl sm:text-3xl text-ink mb-10">
                    <span class="text-brass-500">★</span> {{ number_format($averageRating, 1) }} {{ __('from') }} {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}
                </h2>

                <div class="grid sm:grid-cols-3 gap-6">
                    @foreach ($reviews as $review)
                        <div class="card p-6">
                            <p class="text-brass-500 mb-3">{{ str_repeat('★', $review->rating) }}<span class="text-pine-100">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                            <p class="text-sm text-ink/70">&ldquo;{{ $review->comment }}&rdquo;</p>
                            <p class="mt-4 text-sm font-medium text-ink">
                                {{ \Illuminate\Support\Str::before($review->customer->name, ' ') }} {{ \Illuminate\Support\Str::substr(\Illuminate\Support\Str::after($review->customer->name, ' '), 0, 1) }}.
                                @if ($review->is_verified_booking)
                                    <span class="text-ink/40 font-normal">· {{ __('Verified booking') }}</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($salon?->address || $salon?->phone)
            <section class="bg-pine-950 text-parchment">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <p class="font-display text-sm uppercase tracking-widest text-brass-400 mb-2">{{ __('Visit us') }}</p>
                    <h2 class="font-display text-2xl sm:text-3xl mb-8">{{ __('Come as you are. Leave looking like you.') }}</h2>

                    <div class="grid sm:grid-cols-3 gap-8 text-sm">
                        @if ($salon?->address)
                            <div>
                                <p class="font-display text-brass-400 mb-1">{{ __('Location') }}</p>
                                <p class="text-parchment/70">{{ trim("{$salon->address}, {$salon->city}, {$salon->state} {$salon->zip_code}", ' ,') }}</p>
                            </div>
                        @endif
                        @if ($salon?->opens_at && $salon?->closes_at)
                            <div>
                                <p class="font-display text-brass-400 mb-1">{{ __('Hours') }}</p>
                                <p class="text-parchment/70">{{ $salon->opens_at->format('g:i A') }} – {{ $salon->closes_at->format('g:i A') }}</p>
                            </div>
                        @endif
                        @if ($salon?->phone)
                            <div>
                                <p class="font-display text-brass-400 mb-1">{{ __('Phone') }}</p>
                                <a href="tel:{{ $salon->phone }}" class="text-parchment/70 hover:text-parchment">{{ $salon->phone }}</a>
                            </div>
                        @endif
                    </div>

                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex items-center mt-10 px-6 py-3 rounded-md bg-brass-500 text-pine-950 font-medium hover:bg-brass-400 transition-colors">
                        {{ __('Book an Appointment') }}
                    </a>
                </div>
            </section>
        @endif

        <footer class="bg-pine-950 text-parchment/70 border-t border-parchment/10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="font-display text-lg text-parchment">{{ $salon?->name ?? config('app.name', '1308Studio') }}</p>
                <div class="text-sm space-y-1 sm:text-right">
                    @if ($salon?->address)
                        <p>{{ trim("{$salon->address}, {$salon->city}, {$salon->state} {$salon->zip_code}", ' ,') }}</p>
                    @endif
                    @if ($salon?->phone)
                        <p>{{ $salon->phone }}</p>
                    @endif
                </div>
            </div>
        </footer>
    </body>
</html>
