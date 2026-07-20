<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit :name', ['name' => $service->name]) }}
        </h2>
    </x-slot>

    <div class="max-w-2xl card">
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.services._form')

            <button type="submit" class="btn-primary">
                {{ __('Save Changes') }}
            </button>
        </form>
    </div>
</x-admin-layout>
