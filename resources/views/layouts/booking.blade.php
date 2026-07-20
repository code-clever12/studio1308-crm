<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Book an Appointment') }} - {{ config('app.name', 'Ritual Barber Studio') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|oswald:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-parchment text-ink">
        <div class="min-h-screen flex flex-col">
            <header class="bg-pine-950 text-parchment">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 py-5 flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="font-display text-lg tracking-wide">
                        {{ config('app.name', 'Ritual Barber Studio') }}
                    </a>
                    <a href="{{ route('customer.appointments.index') }}" class="text-sm text-parchment/70 hover:text-parchment">
                        {{ __('Cancel') }}
                    </a>
                </div>

                <!-- Step indicator: a "ticket stub" row of numbered markers -->
                <div class="max-w-3xl mx-auto px-4 sm:px-6 pb-6">
                    <ol class="flex items-center">
                        <template x-for="(label, index) in stepLabels" :key="index">
                            <li class="flex items-center flex-1 last:flex-none">
                                <div class="flex flex-col items-center gap-1.5 shrink-0">
                                    <span
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold font-display border-2 transition-colors"
                                        :class="{
                                            'bg-brass-500 border-brass-500 text-pine-950': step === index + 1,
                                            'bg-pine-800 border-pine-600 text-parchment/90': step > index + 1,
                                            'bg-transparent border-pine-700 text-parchment/50': step < index + 1,
                                        }"
                                        x-text="step > index + 1 ? '✓' : index + 1"
                                    ></span>
                                    <span
                                        class="text-[11px] uppercase tracking-wide hidden sm:block"
                                        :class="step === index + 1 ? 'text-parchment' : 'text-parchment/50'"
                                        x-text="label"
                                    ></span>
                                </div>
                                <div
                                    class="h-0.5 flex-1 mx-2 transition-colors"
                                    x-show="index < stepLabels.length - 1"
                                    :class="step > index + 1 ? 'bg-brass-500' : 'bg-pine-700'"
                                ></div>
                            </li>
                        </template>
                    </ol>
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
