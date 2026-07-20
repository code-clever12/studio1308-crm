@props(['id', 'title', 'action', 'method' => 'DELETE', 'confirmLabel' => 'Confirm'])

<x-modal :name="$id" max-width="md">
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <h2 class="text-lg font-medium text-gray-900">{{ $title }}</h2>

        @if ($slot->isNotEmpty())
            <div class="mt-1 text-sm text-gray-600">{{ $slot }}</div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
            <x-danger-button>{{ $confirmLabel }}</x-danger-button>
        </div>
    </form>
</x-modal>
