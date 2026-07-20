<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Appointments') }}
        </h2>
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <form method="GET" class="flex items-center gap-3">
            <a href="{{ route('admin.appointments.index', ['date' => \Illuminate\Support\Carbon::parse($date)->subDay()->toDateString()]) }}" class="text-gray-400 hover:text-gray-700">&larr;</a>
            <input
                type="date" name="date" value="{{ \Illuminate\Support\Carbon::parse($date)->toDateString() }}"
                onchange="this.form.submit()"
                class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <a href="{{ route('admin.appointments.index', ['date' => \Illuminate\Support\Carbon::parse($date)->addDay()->toDateString()]) }}" class="text-gray-400 hover:text-gray-700">&rarr;</a>
            <a href="{{ route('admin.appointments.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('Today') }}</a>
        </form>

        <a href="{{ route('admin.appointments.create') }}" class="btn-primary">
            {{ __('+ New Booking') }}
        </a>
    </div>

    <p class="text-sm text-gray-500 mb-4">
        {{ __(':count appointments on :date — drag a booking to a new time to reschedule it.', ['count' => $appointments->count(), 'date' => \Illuminate\Support\Carbon::parse($date)->toFormattedDateString()]) }}
    </p>

    <x-calendar-grid :appointments="$appointments" :date="\Illuminate\Support\Carbon::parse($date)->toDateString()" />
</x-admin-layout>
