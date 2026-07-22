@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-pine-200 focus:border-pine-500 focus:ring-pine-500 rounded-md shadow-sm']) }}>
