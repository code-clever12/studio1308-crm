@php
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $existing = $staff->schedules->keyBy('day_of_week');
@endphp

<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __(':name — Weekly Schedule', ['name' => $staff->user->name]) }}
        </h2>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form method="POST" action="{{ route('admin.schedules.update', $staff) }}" class="space-y-3">
                @csrf
                @method('PUT')

                @foreach ($days as $index => $label)
                    @php $day = $existing->get($index); @endphp
                    <div x-data="{ working: {{ $day?->is_working_day ? 'true' : 'false' }} }" class="border border-gray-100 rounded-lg p-4">
                        <input type="hidden" name="schedules[{{ $index }}][day_of_week]" value="{{ $index }}">

                        <label class="flex items-center gap-3 mb-3">
                            <input type="checkbox" name="schedules[{{ $index }}][is_working_day]" value="1" x-model="working" class="rounded border-gray-300 text-indigo-600">
                            <span class="font-medium text-gray-800 w-24">{{ $label }}</span>
                        </label>

                        <div x-show="working" class="grid grid-cols-2 sm:grid-cols-4 gap-3 pl-8">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Start') }}</label>
                                <input type="time" name="schedules[{{ $index }}][start_time]" value="{{ $day?->start_time ? \Illuminate\Support\Carbon::parse($day->start_time)->format('H:i') : '09:00' }}" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('End') }}</label>
                                <input type="time" name="schedules[{{ $index }}][end_time]" value="{{ $day?->end_time ? \Illuminate\Support\Carbon::parse($day->end_time)->format('H:i') : '17:00' }}" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Break Start') }}</label>
                                <input type="time" name="schedules[{{ $index }}][break_start]" value="{{ $day?->break_start ? \Illuminate\Support\Carbon::parse($day->break_start)->format('H:i') : '' }}" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Break End') }}</label>
                                <input type="time" name="schedules[{{ $index }}][break_end]" value="{{ $day?->break_end ? \Illuminate\Support\Carbon::parse($day->break_end)->format('H:i') : '' }}" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">
                    {{ __('Save Schedule') }}
                </button>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Days Off') }}</h3>

            @if ($staff->daysOff->isEmpty())
                <p class="text-sm text-gray-500 mb-4">{{ __('No upcoming days off.') }}</p>
            @else
                <ul class="divide-y divide-gray-100 mb-4">
                    @foreach ($staff->daysOff as $dayOff)
                        <li class="flex items-center justify-between py-2 text-sm">
                            <span>{{ $dayOff->date->toFormattedDateString() }} @if ($dayOff->reason) — {{ $dayOff->reason }} @endif</span>
                            <form method="POST" action="{{ route('admin.schedules.days-off.destroy', [$staff, $dayOff->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">{{ __('Remove') }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('admin.schedules.days-off.store', $staff) }}" class="flex items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('Date') }}</label>
                    <input type="date" name="date" min="{{ now()->toDateString() }}" required class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('Reason') }}</label>
                    <input type="text" name="reason" placeholder="Vacation" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-md bg-gray-800 text-white text-sm font-medium hover:bg-gray-700">
                    {{ __('Add') }}
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
