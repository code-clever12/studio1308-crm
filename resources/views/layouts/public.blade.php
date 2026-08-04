<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#0e1f17">

        <title>{{ $title ? "{$title} · {$siteName}" : $siteName }}</title>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ url()->current() }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $title ?: $siteName }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url()->current() }}">
        @if ($image)
            <meta property="og:image" content="{{ $image }}">
        @endif

        <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $title ?: $siteName }}">
        <meta name="twitter:description" content="{{ $description }}">
        @if ($image)
            <meta name="twitter:image" content="{{ $image }}">
        @endif

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        {{-- Preconnect (not just DNS-prefetch) since the stylesheet below
             pulls the actual font files from this host on every page load —
             saves a full connection round-trip on the critical rendering path. --}}
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|oswald:500,600,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @if ($structuredData)
            <script type="application/ld+json">{!! json_encode($structuredData) !!}</script>
        @endif

        {{ $head ?? '' }}
    </head>
    <body class="font-sans antialiased bg-parchment text-ink">
        <header class="bg-pine-950 text-parchment">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
                <a href="{{ route('welcome') }}" class="font-display text-xl tracking-wide">
                    {{ $siteName }}
                </a>

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

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-pine-950 text-parchment/70 border-t border-parchment/10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="font-display text-lg text-parchment">{{ $siteName }}</p>
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
