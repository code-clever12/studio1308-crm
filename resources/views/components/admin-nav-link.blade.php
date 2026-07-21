@props(['route', 'active' => null])

@php
    $isActive = request()->routeIs($active ?? $route);
@endphp

<a
    href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'flex items-center px-3 py-2 rounded-md text-sm font-medium '.($isActive ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white')]) }}
>
    {{ $slot }}
</a>
