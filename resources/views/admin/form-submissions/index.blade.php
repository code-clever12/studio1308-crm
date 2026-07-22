<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Form Submissions') }}
        </h2>
    </x-slot>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Form') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Slug') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Submissions') }}</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($forms as $form)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $form->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $form->slug }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $form->submissions_count }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.form-submissions.show', $form) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                            {{ __('No form submissions yet. They\'ll show up here as soon as a landing page posts to /api/v1/submit-form.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
