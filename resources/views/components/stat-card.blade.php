@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="text-sm text-gray-500">{{ $label }}</div>
    <div class="text-2xl font-semibold text-gray-900 mt-1">{{ $value }}</div>
    @isset($sub)
        <div class="text-xs text-gray-400 mt-1">{{ $sub }}</div>
    @endisset
</div>
