<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Service') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl card">
        <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-5">
            @csrf
            @include('admin.services._form')

            <button type="submit" class="btn-primary">
                {{ __('Create Service') }}
            </button>
        </form>
    </div>
</x-admin-layout>
