<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $customer->name }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card :label="__('No-Shows')" :value="$noShowCount" />
        <x-stat-card :label="__('Loyalty Points')" :value="$customer->loyaltyPoints?->balance ?? 0" />
        <x-stat-card :label="__('Total Appointments')" :value="$appointments->count()" />
        <div class="card flex flex-col justify-between">
            <div class="text-sm text-gray-500 mb-2">{{ __('Booking Status') }}</div>
            @if ($customer->is_active)
                <button type="button" x-data x-on:click="$dispatch('open-modal', 'block-customer')" class="text-sm font-medium text-red-600 hover:underline text-left">
                    {{ __('Block Customer') }}
                </button>

                <x-confirm-modal
                    id="block-customer"
                    :title="__('Block this customer from booking?')"
                    :action="route('admin.customers.block', $customer)"
                    method="POST"
                    :confirm-label="__('Block Customer')"
                >
                    {{ __('They will no longer be able to book appointments or join the waitlist until unblocked.') }}
                </x-confirm-modal>
            @else
                <form method="POST" action="{{ route('admin.customers.unblock', $customer) }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-green-700 hover:underline">{{ __('Unblock Customer') }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card mb-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Contact') }}</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            <div><dt class="text-gray-500">{{ __('Email') }}</dt><dd class="text-gray-900">{{ $customer->email }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Phone') }}</dt><dd class="text-gray-900">{{ $customer->phone ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Member Since') }}</dt><dd class="text-gray-900">{{ $customer->created_at->toFormattedDateString() }}</dd></div>
        </dl>
    </div>

    <div class="data-table-wrapper">
        <h3 class="text-sm font-semibold text-gray-800 px-6 py-4 border-b border-gray-100">{{ __('Booking History') }}</h3>
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Service') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Staff') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $appointment->appointment_date->toFormattedDateString() }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $appointment->service->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $appointment->staff?->user?->name ?? '—' }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$appointment->status" /></td>
                        <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($appointment->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No bookings yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
