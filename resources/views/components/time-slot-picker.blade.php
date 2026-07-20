@props(['times' => 'availableTimes', 'selected' => 'startTime', 'select' => 'selectTime'])

<div {{ $attributes->merge(['class' => 'grid grid-cols-3 sm:grid-cols-4 gap-2']) }}>
    <template x-for="time in {{ $times }}" :key="time">
        <button
            type="button"
            @click="{{ $select }}(time)"
            :class="{{ $selected }} === time
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white text-gray-700 border-gray-300 hover:border-indigo-400'"
            class="border rounded-md py-2 text-sm font-medium transition-colors"
            x-text="time"
        ></button>
    </template>

    <p x-show="{{ $times }}.length === 0" class="col-span-full text-sm text-gray-500 py-4 text-center">
        {{ __('No times available — try another date.') }}
    </p>
</div>
