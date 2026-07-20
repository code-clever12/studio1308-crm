<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ACH Payouts') }}
        </h2>
    </x-slot>

    <div class="card mb-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Run Batch Payout') }}</h3>
        <p class="text-xs text-gray-500 mb-4">
            {{ __('Creates a pending payout for every staff member with a verified ACH account and earnings in this period. Actual bank transfers are wired up in Step 8.') }}
        </p>
        <form method="POST" action="{{ route('admin.payouts.store') }}" class="flex items-end gap-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ now()->subWeek()->toDateString() }}" required class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ now()->toDateString() }}" required class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button type="submit" class="btn-primary">
                {{ __('Run Payouts') }}
            </button>
        </form>
    </div>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Staff') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Commission') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Tips') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($payouts as $payout)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payout->staff->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($payout->commission_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($payout->tips_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">${{ number_format($payout->amount, 2) }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$payout->status" /></td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payout->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No payouts yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
