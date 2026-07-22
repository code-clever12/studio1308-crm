<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '1308Studio') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|oswald:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-parchment">
            <a href="{{ route('welcome') }}" class="font-display text-2xl tracking-wide text-pine-900">
                {{ config('app.name', '1308Studio') }}
            </a>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
