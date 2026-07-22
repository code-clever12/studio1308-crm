<x-admin-layout>
    <x-slot name="header">
        <a href="{{ route('admin.form-submissions.show', $submission->form) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ __('Back to :form', ['form' => $submission->form->name]) }}</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-1">
            {{ __('Edit Lead') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl card">
        <form method="POST" action="{{ route('admin.form-submissions.update', $submission) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @foreach ($submission->payload ?? [] as $key => $value)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                    <input type="text" name="payload[{{ $key }}]" value="{{ old('payload.'.$key, $value) }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            @endforeach

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Value ($)') }}</label>
                    <input type="number" name="value" value="{{ old('value', $submission->value) }}" min="0" step="0.01" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                    <select name="status" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (\App\Models\FormSubmission::STATUSES as $status)
                            <option value="{{ $status }}" @selected(old('status', $submission->status) === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Source URL') }}</label>
                <input type="text" name="url" value="{{ old('url', $submission->url) }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit" class="btn-primary">
                {{ __('Save Changes') }}
            </button>
        </form>
    </div>
</x-admin-layout>
