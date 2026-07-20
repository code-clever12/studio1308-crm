<x-app-layout>
    <x-slot name="header">
        <p class="font-display text-sm uppercase tracking-widest text-brass-400">{{ __('Generosity') }}</p>
        <h2 class="font-display text-3xl mt-1">{{ __('Tips History') }}</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if ($tips->isEmpty())
            <p class="text-ink/60">{{ __("You haven't left any tips yet.") }}</p>
        @else
            <div class="bg-white border border-pine-100 rounded-xl divide-y divide-pine-100 overflow-hidden">
                @foreach ($tips as $tip)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <p class="font-medium text-ink">{{ $tip->staff?->user?->name ?? __('Staff member') }}</p>
                            <p class="text-sm text-ink/60">
                                {{ $tip->appointment?->service?->name }} &middot; {{ $tip->created_at->toFormattedDateString() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-lg text-pine-800">${{ number_format($tip->amount, 2) }}</p>
                            @if ($tip->percentage)
                                <p class="text-xs text-ink/50">{{ (int) $tip->percentage }}%</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-sm text-ink/60">
                {{ __('Total tipped') }}:
                <span class="font-medium text-ink">${{ number_format($tips->sum('amount'), 2) }}</span>
            </p>
        @endif
    </div>
</x-app-layout>
