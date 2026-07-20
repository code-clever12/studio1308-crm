@props(['service'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-pine-100 shadow-sm p-5 flex flex-col']) }}>
    @if ($service->category)
        <span class="text-xs uppercase tracking-wide text-brass-600 font-medium">{{ $service->category->name }}</span>
    @endif

    <h3 class="font-display text-lg text-ink mt-1">{{ $service->name }}</h3>

    @if ($service->description)
        <p class="text-sm text-ink/60 mt-2 flex-1 line-clamp-2">{{ $service->description }}</p>
    @endif

    <div class="mt-4 flex items-center justify-between text-sm">
        <span class="text-ink/60">{{ $service->duration_minutes }} min</span>
        <span class="font-display text-lg text-pine-800">${{ number_format($service->price, 2) }}</span>
    </div>

    @isset($actions)
        <div class="mt-4">
            {{ $actions }}
        </div>
    @endisset
</div>
