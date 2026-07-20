@props(['staff'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-pine-100 shadow-sm p-5 text-center']) }}>
    <div class="mx-auto w-16 h-16 rounded-full bg-pine-100 text-pine-800 flex items-center justify-center font-display text-xl">
        {{ collect(explode(' ', $staff->user->name))->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}
    </div>

    <h3 class="mt-3 font-display text-lg text-ink">{{ $staff->user->name }}</h3>

    @if ($staff->reviews_avg_rating)
        <p class="text-sm text-brass-600 mt-0.5">
            {{ '★' }} {{ number_format($staff->reviews_avg_rating, 1) }}
        </p>
    @endif

    @if ($staff->bio)
        <p class="text-sm text-ink/60 mt-2 line-clamp-3">{{ $staff->bio }}</p>
    @endif
</div>
