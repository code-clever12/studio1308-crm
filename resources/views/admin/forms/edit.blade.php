<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit :name', ['name' => $form->name]) }}
        </h2>
    </x-slot>

    <div class="max-w-2xl card">
        <form method="POST" action="{{ route('admin.forms.update', $form) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $form->name) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                <textarea name="description" rows="2" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $form->description) }}</textarea>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $form->is_active)) class="rounded border-gray-300 text-indigo-600">
                {{ __('Active') }}
            </label>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Fields') }}</label>
                <x-form-builder :fields="$form->fields_json ?? []" />
            </div>

            <button type="submit" class="btn-primary">
                {{ __('Save Changes') }}
            </button>
        </form>
    </div>
</x-admin-layout>
