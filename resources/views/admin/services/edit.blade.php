<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit :name', ['name' => $service->name]) }}
        </h2>
    </x-slot>

    <div class="max-w-2xl bg-white shadow-sm rounded-lg p-6">
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.services._form')

            <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">
                {{ __('Save Changes') }}
            </button>
        </form>
    </div>
</x-admin-layout>
