<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat Leads') }}
        </h2>
    </x-slot>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Form') }}</th>
                    @foreach ($payloadKeys as $key)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('UTM') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Value') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Source URL') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Submitted') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Capture') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($submissions as $submission)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                            <a href="{{ route('admin.form-submissions.show', $submission->form) }}" class="text-indigo-600 hover:text-indigo-800">{{ $submission->form->name }}</a>
                        </td>
                        @foreach ($payloadKeys as $key)
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $submission->payload[$key] ?? '—' }}</td>
                        @endforeach
                        <td class="px-6 py-4 text-xs text-gray-500">
                            @forelse ($submission->utm_parameters ?? [] as $utmKey => $utmValue)
                                <div>{{ str_replace('utm_', '', $utmKey) }}: {{ $utmValue }}</div>
                            @empty
                                &mdash;
                            @endforelse
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $submission->value !== null ? '$'.number_format($submission->value, 2) : '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $submission->url }}">
                            @if ($submission->url)
                                <a href="{{ $submission->url }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800">{{ \Illuminate\Support\Str::limit($submission->url, 40) }}</a>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $submission->submission_time->format('M j, Y g:i A') }}</td>
                        <td class="px-6 py-4">
                            <span @class([
                                'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                'bg-green-50 text-green-700' => $submission->capture_status === 'completed',
                                'bg-gray-100 text-gray-600' => $submission->capture_status === 'abandoned',
                            ])>
                                {{ ucfirst($submission->capture_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.form-submissions.update-status', $submission) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" @class([
                                    'rounded-md text-sm focus:ring-indigo-500 border-2',
                                    'border-blue-200 bg-blue-50 text-blue-700 focus:border-blue-500' => $submission->status === 'cold_lead',
                                    'border-amber-200 bg-amber-50 text-amber-700 focus:border-amber-500' => $submission->status === 'warm_lead',
                                    'border-red-200 bg-red-50 text-red-700 focus:border-red-500' => $submission->status === 'hot_lead',
                                ])>
                                    @foreach (\App\Models\FormSubmission::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($submission->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap space-x-3">
                            <a href="{{ route('admin.form-submissions.edit', $submission) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-submission-{{ $submission->id }}')" class="text-red-600 hover:text-red-800">
                                {{ __('Delete') }}
                            </button>

                            <x-confirm-modal
                                id="delete-submission-{{ $submission->id }}"
                                :title="__('Delete this lead?')"
                                :action="route('admin.form-submissions.destroy', $submission)"
                                :confirm-label="__('Delete')"
                            >
                                {{ __('This permanently removes the submission. This cannot be undone.') }}
                            </x-confirm-modal>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $payloadKeys->count() + 8 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                            {{ __('No submissions yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>
</x-admin-layout>
