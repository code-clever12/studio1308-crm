@props(['appointment'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-pine-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center gap-4']) }}>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <h3 class="font-display text-lg text-ink">{{ $appointment->service->name }}</h3>
            <x-status-badge :status="$appointment->status" />
        </div>

        <dl class="mt-2 text-sm text-ink/70 space-y-0.5">
            <div class="flex gap-1.5">
                <dt class="font-medium text-ink/90">{{ $appointment->appointment_date->toFormattedDateString() }}</dt>
                <dd>at {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('g:i A') }}</dd>
            </div>
            @if ($appointment->staff)
                <div>{{ __('with :name', ['name' => $appointment->staff->user->name]) }}</div>
            @endif
            <div>${{ number_format($appointment->total_amount, 2) }}</div>
        </dl>
    </div>

    @isset($actions)
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
