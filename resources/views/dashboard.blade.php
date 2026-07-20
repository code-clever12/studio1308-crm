<x-app-layout>
    <x-slot name="header">
        <p class="font-display text-sm uppercase tracking-widest text-brass-400">{{ __('Welcome back') }}</p>
        <h2 class="font-display text-3xl mt-1">{{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if (auth()->user()->isCustomer())
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
                <div class="bg-white border border-pine-100 rounded-xl p-5">
                    <p class="text-sm text-ink/60">{{ __('Upcoming Appointments') }}</p>
                    <p class="font-display text-2xl text-pine-800 mt-1">{{ $upcoming->count() }}</p>
                </div>
                <div class="bg-white border border-pine-100 rounded-xl p-5">
                    <p class="text-sm text-ink/60">{{ __('Loyalty Points') }}</p>
                    <p class="font-display text-2xl text-pine-800 mt-1">{{ $loyaltyPoints?->balance ?? 0 }}</p>
                </div>
                <div class="bg-white border border-pine-100 rounded-xl p-5">
                    <p class="text-sm text-ink/60">{{ __('Waitlist Spots') }}</p>
                    <p class="font-display text-2xl text-pine-800 mt-1">{{ $waitlistCount }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-xl text-ink">{{ __('Your Next Visits') }}</h3>
                <a href="{{ route('customer.booking.create') }}" class="text-sm font-medium text-pine-700 hover:underline">
                    {{ __('+ Book another') }}
                </a>
            </div>

            @if ($upcoming->isEmpty())
                <div class="bg-white border border-dashed border-pine-200 rounded-xl p-8 text-center">
                    <p class="text-ink/60 mb-4">{{ __("You don't have any upcoming appointments.") }}</p>
                    <a href="{{ route('customer.booking.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-md bg-pine-800 text-parchment text-sm font-medium hover:bg-pine-700">
                        {{ __('Book Your First Appointment') }}
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($upcoming as $appointment)
                        <x-appointment-card :appointment="$appointment" />
                    @endforeach
                </div>

                <a href="{{ route('customer.appointments.index') }}" class="inline-block mt-4 text-sm text-pine-700 hover:underline">
                    {{ __('View all appointments →') }}
                </a>
            @endif
        @else
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in as staff.") }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
