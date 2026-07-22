<x-admin-layout>
    <x-slot name="header">
        <a href="{{ route('admin.form-submissions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ __('All Forms') }}</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-1">
            {{ $form->name }}
        </h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('Slug') }}: {{ $form->slug }}</p>
    </x-slot>

    <div class="data-table-wrapper">
        <table class="data-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach ($payloadKeys as $key)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('UTM') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Value') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Source URL') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Submitted') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($submissions as $submission)
                    <tr>
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
                            <form method="POST" action="{{ route('admin.form-submissions.update-status', $submission) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (['new', 'contacted', 'converted', 'archived'] as $status)
                                        <option value="{{ $status }}" @selected($submission->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $payloadKeys->count() + 5 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                            {{ __('No submissions for this form yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
