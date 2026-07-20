<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Consent Forms') }}
        </h2>
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.forms.create') }}" class="btn-primary">
            {{ __('+ New Form') }}
        </a>
    </div>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Fields') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($forms as $form)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $form->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ count($form->fields_json ?? []) }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$form->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <a href="{{ route('admin.forms.responses', $form) }}" class="text-gray-500 hover:text-gray-800">{{ __('Responses') }}</a>
                            <a href="{{ route('admin.forms.edit', $form) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No consent forms yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
