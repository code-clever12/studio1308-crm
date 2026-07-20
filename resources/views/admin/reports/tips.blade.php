<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tips Report') }}
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
        <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">
            {{ __('Filter') }}
        </button>
    </form>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Staff') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Tips Received') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tipsByStaff as $row)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['staff_name'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $row['tip_count'] }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">${{ number_format($row['total_tips'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No tips recorded in this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
