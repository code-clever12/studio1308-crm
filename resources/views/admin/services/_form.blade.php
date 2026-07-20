@php $service ??= null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
        <input type="text" name="name" value="{{ old('name', $service?->name) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
        <select name="category_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $service?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
    <textarea name="description" rows="3" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $service?->description) }}</textarea>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Price ($)') }}</label>
        <input type="number" name="price" value="{{ old('price', $service?->price) }}" min="0" step="0.01" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Duration (min)') }}</label>
        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service?->duration_minutes) }}" min="15" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Buffer (min)') }}</label>
        <input type="number" name="buffer_time_minutes" value="{{ old('buffer_time_minutes', $service?->buffer_time_minutes ?? 15) }}" min="0" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deposit override ($)') }}</label>
        <input type="number" name="deposit_amount" value="{{ old('deposit_amount', $service?->deposit_amount) }}" min="0" step="0.01" placeholder="{{ __('Uses salon default') }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Consent Form') }}</label>
    <select name="consent_form_id" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('None') }}</option>
        @foreach ($consentForms as $form)
            <option value="{{ $form->id }}" @selected(old('consent_form_id', $service?->consent_form_id) == $form->id)>{{ $form->name }}</option>
        @endforeach
    </select>
</div>

<div class="flex flex-wrap gap-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="requires_consent_form" value="0">
        <input type="checkbox" name="requires_consent_form" value="1" @checked(old('requires_consent_form', $service?->requires_consent_form)) class="rounded border-gray-300 text-indigo-600">
        {{ __('Requires consent form') }}
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_taxable" value="0">
        <input type="checkbox" name="is_taxable" value="1" @checked(old('is_taxable', $service?->is_taxable ?? true)) class="rounded border-gray-300 text-indigo-600">
        {{ __('Taxable') }}
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service?->is_active ?? true)) class="rounded border-gray-300 text-indigo-600">
        {{ __('Active') }}
    </label>
</div>
