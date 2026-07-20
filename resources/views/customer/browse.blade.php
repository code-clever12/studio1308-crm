<x-app-layout>
    <x-slot name="header">
        <p class="font-display text-sm tracking-widest uppercase text-brass-400">{{ __('The Studio') }}</p>
        <h2 class="font-display text-3xl mt-1">{{ __('Browse Services & Barbers') }}</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-10">
            <input
                type="search" name="search" value="{{ request('search') }}"
                placeholder="{{ __('Search services…') }}"
                class="flex-1 rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500"
            >
            <select name="category_id" class="rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <input
                type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="1"
                placeholder="{{ __('Max price') }}"
                class="w-32 rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500"
            >
            <button type="submit" class="inline-flex items-center justify-center px-5 py-2 rounded-md bg-pine-800 text-parchment text-sm font-medium hover:bg-pine-700 transition-colors">
                {{ __('Filter') }}
            </button>
        </form>

        <section>
            <h3 class="font-display text-xl text-ink mb-4">{{ __('Services') }}</h3>

            @if ($services->isEmpty())
                <p class="text-ink/60">{{ __('No services match your search.') }}</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($services as $service)
                        <x-service-card :service="$service">
                            <x-slot name="actions">
                                <a
                                    href="{{ route('customer.booking.create', ['service' => $service->id]) }}"
                                    class="block text-center w-full rounded-md bg-pine-800 text-parchment text-sm font-medium py-2 hover:bg-pine-700 transition-colors"
                                >
                                    {{ __('Book Now') }}
                                </a>
                            </x-slot>
                        </x-service-card>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-14">
            <h3 class="font-display text-xl text-ink mb-4">{{ __('Our Barbers') }}</h3>

            @if ($staff->isEmpty())
                <p class="text-ink/60">{{ __('No barbers listed yet.') }}</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach ($staff as $member)
                        <x-staff-card :staff="$member" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
