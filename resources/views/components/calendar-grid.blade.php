@props(['appointments', 'date'])

@php
    $salon = \App\Models\Salon::query()->first();
    $slots = [];

    if ($salon) {
        $cursor = \Illuminate\Support\Carbon::parse($salon->opens_at);
        $close = \Illuminate\Support\Carbon::parse($salon->closes_at);

        while ($cursor->lt($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes(30);
        }
    }

    $statusColors = [
        'pending' => 'bg-amber-50 border-amber-300 text-amber-900',
        'confirmed' => 'bg-green-50 border-green-300 text-green-900',
        'in_progress' => 'bg-blue-50 border-blue-300 text-blue-900',
        'completed' => 'bg-emerald-50 border-emerald-300 text-emerald-900',
        'cancelled' => 'bg-red-50 border-red-300 text-red-900 line-through opacity-60',
        'no_show' => 'bg-gray-100 border-gray-300 text-gray-600',
    ];
@endphp

<div
    x-data="{
        dragging: null,
        onDrop(time) {
            if (! this.dragging) return;
            const id = this.dragging;
            this.dragging = null;

            fetch(`/admin/appointments/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ appointment_date: '{{ $date }}', start_time: time }),
            }).then((response) => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('That time is not available.');
                }
            });
        },
    }"
    class="border border-gray-200 rounded-lg overflow-hidden bg-white divide-y divide-gray-100"
>
    @forelse ($slots as $time)
        @php
            $slotAppointments = $appointments->filter(
                fn ($appointment) => \Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i') === $time
            );
        @endphp
        <div
            class="flex items-stretch min-h-12"
            @dragover.prevent
            @drop="onDrop('{{ $time }}:00')"
        >
            <div class="w-20 shrink-0 py-2.5 px-3 text-xs text-gray-400 border-r border-gray-100">
                {{ \Illuminate\Support\Carbon::parse($time)->format('g:i A') }}
            </div>
            <div class="flex-1 flex flex-wrap items-center gap-2 p-2">
                @foreach ($slotAppointments as $appointment)
                    <div
                        draggable="true"
                        @dragstart="dragging = {{ $appointment->id }}"
                        class="cursor-move select-none rounded-md border px-3 py-1.5 text-xs font-medium {{ $statusColors[$appointment->status] ?? 'bg-gray-50 border-gray-300 text-gray-700' }}"
                        title="{{ __('Drag to reschedule') }}"
                    >
                        {{ $appointment->customer->name }} &middot; {{ $appointment->service->name }}
                        @if ($appointment->staff)
                            <span class="opacity-60">({{ $appointment->staff->user->name }})</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="p-6 text-sm text-gray-500">{{ __('No operating hours configured for the salon yet.') }}</p>
    @endforelse
</div>
