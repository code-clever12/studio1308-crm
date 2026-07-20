<x-app-layout>
    <x-slot name="header">
        <p class="font-display text-sm uppercase tracking-widest text-brass-400">{{ __('Your Bookings') }}</p>
        <h2 class="font-display text-3xl mt-1">{{ __('My Appointments') }}</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ tab: 'upcoming' }">
        <div class="flex gap-1 border-b border-pine-100 mb-6">
            <button
                type="button" @click="tab = 'upcoming'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                :class="tab === 'upcoming' ? 'border-pine-700 text-pine-800' : 'border-transparent text-ink/50 hover:text-ink/80'"
            >
                {{ __('Upcoming') }} ({{ $upcoming->count() }})
            </button>
            <button
                type="button" @click="tab = 'past'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                :class="tab === 'past' ? 'border-pine-700 text-pine-800' : 'border-transparent text-ink/50 hover:text-ink/80'"
            >
                {{ __('Past') }} ({{ $past->count() }})
            </button>
            <button
                type="button" @click="tab = 'cancelled'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                :class="tab === 'cancelled' ? 'border-pine-700 text-pine-800' : 'border-transparent text-ink/50 hover:text-ink/80'"
            >
                {{ __('Cancelled') }} ({{ $cancelled->count() }})
            </button>
        </div>

        {{-- Upcoming --}}
        <div x-show="tab === 'upcoming'" class="space-y-4">
            @forelse ($upcoming as $appointment)
                <div x-data="{ showReschedule: false }">
                    <x-appointment-card :appointment="$appointment">
                        <x-slot name="actions">
                            <button type="button" @click="showReschedule = ! showReschedule" class="text-sm text-pine-700 hover:underline">
                                {{ __('Reschedule') }}
                            </button>
                            <button type="button" x-on:click="$dispatch('open-modal', 'cancel-appointment-{{ $appointment->id }}')" class="text-sm text-red-600 hover:underline">
                                {{ __('Cancel') }}
                            </button>
                        </x-slot>
                    </x-appointment-card>

                    <x-confirm-modal
                        id="cancel-appointment-{{ $appointment->id }}"
                        :title="__('Cancel this appointment?')"
                        :action="route('customer.appointments.destroy', $appointment)"
                        :confirm-label="__('Cancel Appointment')"
                    >
                        {{ __('Your deposit will be refunded minus any late-cancellation fee, per the salon\'s cancellation policy.') }}
                    </x-confirm-modal>

                    <div x-show="showReschedule" x-cloak class="bg-pine-50 border border-pine-100 border-t-0 rounded-b-lg p-4 -mt-2">
                        <form method="POST" action="{{ route('customer.appointments.update', $appointment) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-xs text-ink/60 mb-1">{{ __('New Date') }}</label>
                                <input type="date" name="appointment_date" min="{{ now()->toDateString() }}" required class="rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500">
                            </div>
                            <div>
                                <label class="block text-xs text-ink/60 mb-1">{{ __('New Time') }}</label>
                                <input type="time" name="start_time" required class="rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500">
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-md bg-pine-800 text-parchment text-sm font-medium hover:bg-pine-700">
                                {{ __('Save') }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-ink/60">
                    {{ __('No upcoming appointments.') }}
                    <a href="{{ route('customer.booking.create') }}" class="text-pine-700 underline">{{ __('Book one now') }}</a>.
                </p>
            @endforelse
        </div>

        {{-- Past --}}
        <div x-show="tab === 'past'" x-cloak class="space-y-4">
            @forelse ($past as $appointment)
                <div>
                    <x-appointment-card :appointment="$appointment" />

                    @if ($appointment->status === 'completed' && ! $appointment->review && $appointment->staff_id)
                        <div class="bg-white border border-pine-100 border-t-0 rounded-b-lg p-4 -mt-2" x-data="{ rating: 5 }">
                            <form method="POST" action="{{ route('customer.appointments.review', $appointment) }}">
                                @csrf
                                <p class="text-sm font-medium text-ink mb-2">{{ __('Rate this visit') }}</p>
                                <div class="flex gap-1 mb-3">
                                    <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                        <button type="button" @click="rating = star" class="text-2xl leading-none" :class="star <= rating ? 'text-brass-500' : 'text-pine-200'">★</button>
                                    </template>
                                </div>
                                <input type="hidden" name="rating" :value="rating">
                                <textarea name="comment" rows="2" placeholder="{{ __('Optional comment…') }}" class="w-full rounded-md border-pine-200 text-sm mb-3 focus:border-pine-500 focus:ring-pine-500"></textarea>
                                <button type="submit" class="px-4 py-2 rounded-md bg-pine-800 text-parchment text-sm font-medium hover:bg-pine-700">
                                    {{ __('Submit Review') }}
                                </button>
                            </form>
                        </div>
                    @elseif ($appointment->review)
                        <div class="bg-pine-50 border border-pine-100 border-t-0 rounded-b-lg p-4 -mt-2 text-sm text-ink/70">
                            {{ __('You rated this') }} {{ $appointment->review->rating }}/5
                            @if ($appointment->review->comment)
                                — "{{ $appointment->review->comment }}"
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-ink/60">{{ __('No past appointments yet.') }}</p>
            @endforelse
        </div>

        {{-- Cancelled --}}
        <div x-show="tab === 'cancelled'" x-cloak class="space-y-4">
            @forelse ($cancelled as $appointment)
                <x-appointment-card :appointment="$appointment" />
            @empty
                <p class="text-ink/60">{{ __('No cancelled appointments.') }}</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
