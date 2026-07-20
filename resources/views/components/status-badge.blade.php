@props(['status'])

@php
    $map = [
        'pending' => ['bg-amber-100', 'text-amber-800', 'Pending'],
        'confirmed' => ['bg-green-100', 'text-green-800', 'Confirmed'],
        'in_progress' => ['bg-blue-100', 'text-blue-800', 'In Progress'],
        'completed' => ['bg-emerald-100', 'text-emerald-800', 'Completed'],
        'cancelled' => ['bg-red-100', 'text-red-800', 'Cancelled'],
        'no_show' => ['bg-gray-200', 'text-gray-700', 'No Show'],
        'waiting' => ['bg-amber-100', 'text-amber-800', 'Waiting'],
        'notified' => ['bg-blue-100', 'text-blue-800', 'Notified'],
        'booked' => ['bg-green-100', 'text-green-800', 'Booked'],
        'expired' => ['bg-gray-200', 'text-gray-700', 'Expired'],
        'paid' => ['bg-green-100', 'text-green-800', 'Paid'],
        'partial' => ['bg-amber-100', 'text-amber-800', 'Partial'],
        'refunded' => ['bg-gray-200', 'text-gray-700', 'Refunded'],
        'succeeded' => ['bg-green-100', 'text-green-800', 'Succeeded'],
        'failed' => ['bg-red-100', 'text-red-800', 'Failed'],
        'active' => ['bg-green-100', 'text-green-800', 'Active'],
        'inactive' => ['bg-gray-200', 'text-gray-700', 'Inactive'],
        'on_leave' => ['bg-amber-100', 'text-amber-800', 'On Leave'],
        'in_transit' => ['bg-blue-100', 'text-blue-800', 'In Transit'],
        'verified' => ['bg-green-100', 'text-green-800', 'Verified'],
    ];

    [$bg, $text, $label] = $map[$status] ?? ['bg-gray-200', 'text-gray-700', ucfirst(str_replace('_', ' ', $status))];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap $bg $text"]) }}>
    {{ $label }}
</span>
