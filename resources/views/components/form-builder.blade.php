@props(['fields' => []])

@php
    $initialFields = collect($fields)->map(fn ($field) => [
        'id' => $field['id'] ?? '',
        'label' => $field['label'] ?? '',
        'type' => $field['type'] ?? 'text',
        'required' => $field['required'] ?? true,
        'options' => is_array($field['options'] ?? null) ? implode(', ', $field['options']) : ($field['options'] ?? ''),
    ])->values()->all();

    if (empty($initialFields)) {
        $initialFields = [['id' => '', 'label' => '', 'type' => 'text', 'required' => true, 'options' => '']];
    }
@endphp

<div x-data="{
    fields: {{ \Illuminate\Support\Js::from($initialFields) }},
    addField() {
        this.fields.push({ id: '', label: '', type: 'text', required: true, options: '' });
    },
    removeField(index) {
        this.fields.splice(index, 1);
    },
}">
    <div class="space-y-4">
        <template x-for="(field, index) in fields" :key="index">
            <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Field ID') }}</label>
                        <input type="text" :name="`fields[${index}][id]`" x-model="field.id" placeholder="e.g. allergies"
                               class="w-full rounded-md border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Question / Label') }}</label>
                        <input type="text" :name="`fields[${index}][label]`" x-model="field.label"
                               class="w-full rounded-md border-gray-300 text-sm" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Type') }}</label>
                        <select :name="`fields[${index}][type]`" x-model="field.type" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="text">{{ __('Text') }}</option>
                            <option value="email">{{ __('Email') }}</option>
                            <option value="checkbox">{{ __('Checkbox') }}</option>
                            <option value="radio">{{ __('Radio') }}</option>
                            <option value="date">{{ __('Date') }}</option>
                            <option value="file">{{ __('File') }}</option>
                        </select>
                    </div>
                    <div x-show="field.type === 'radio'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Options (comma separated)') }}</label>
                        <input type="text" :name="`fields[${index}][options]`" x-model="field.options" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" :name="`fields[${index}][required]`" value="0">
                            <input type="checkbox" :name="`fields[${index}][required]`" value="1" x-model="field.required" class="rounded border-gray-300">
                            {{ __('Required') }}
                        </label>
                        <button type="button" @click="removeField(index)" class="text-sm text-red-600 hover:text-red-800">
                            {{ __('Remove') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <button type="button" @click="addField()"
            class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
        {{ __('+ Add Field') }}
    </button>
</div>
