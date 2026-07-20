<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Staff') }}
        </h2>
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.staff.create') }}" class="btn-primary">
            {{ __('+ Add Staff') }}
        </a>
    </div>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Email') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Commission') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($staff as $member)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $member->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $member->user->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ (int) $member->commission_rate }}%</td>
                        <td class="px-6 py-4"><x-status-badge :status="$member->status" /></td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <a href="{{ route('admin.schedules.edit', $member) }}" class="text-gray-500 hover:text-gray-800">{{ __('Schedule') }}</a>
                            <a href="{{ route('admin.staff.edit', $member) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No staff members yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
