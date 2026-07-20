<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
        <div class="p-6 text-gray-900">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="text-sm text-gray-500">{{ __("Today's Appointments") }}</div>
            <div class="text-2xl font-semibold text-gray-900">{{ $stats['todays_appointments'] }}</div>
        </div>
        <div class="card">
            <div class="text-sm text-gray-500">{{ __("Today's Revenue") }}</div>
            <div class="text-2xl font-semibold text-gray-900">${{ number_format($stats['todays_revenue'], 2) }}</div>
        </div>
        <div class="card">
            <div class="text-sm text-gray-500">{{ __('Active Customers') }}</div>
            <div class="text-2xl font-semibold text-gray-900">{{ $stats['active_customers'] }}</div>
        </div>
        <div class="card">
            <div class="text-sm text-gray-500">{{ __('Upcoming (7 days)') }}</div>
            <div class="text-2xl font-semibold text-gray-900">{{ $stats['upcoming_appointments'] }}</div>
        </div>
    </div>

    <div class="card">
        <h3 class="text-sm font-medium text-gray-500 mb-4">{{ __('Top Services This Month') }}</h3>

        @if ($topServices->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No bookings yet this month.') }}</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($topServices as $service)
                    <li class="py-2 flex justify-between text-sm">
                        <span class="text-gray-900">{{ $service->name }}</span>
                        <span class="text-gray-500">{{ $service->appointments_count }} {{ __('bookings') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-admin-layout>
