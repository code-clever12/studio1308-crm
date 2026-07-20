<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sales Tax Report') }}
        </h2>
    </x-slot>

    <form method="GET" class="flex items-end gap-3 mb-6">
        <div>
            <label class="block text-xs text-gray-500 mb-1">{{ __('From') }}</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">{{ __('To') }}</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <button type="submit" class="btn-primary">
            {{ __('Filter') }}
        </button>
    </form>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('State') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Transactions') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Taxable Amount') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Tax Collected') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($report as $row)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['state'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $row['transaction_count'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($row['taxable_amount'], 2) }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">${{ number_format($row['tax_collected'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No tax collected in this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
