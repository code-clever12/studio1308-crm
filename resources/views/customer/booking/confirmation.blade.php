<x-app-layout>
    <x-slot name="header">
        <p class="font-display text-sm uppercase tracking-widest text-brass-400">{{ __('Booking') }}</p>
        <h2 class="font-display text-3xl mt-1">{{ __('Confirmation') }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if ($redirectStatus === 'requires_payment_method')
            <div class="card p-6 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-2xl mb-4">!</div>
                <p class="font-display text-lg text-ink mb-2">{{ __("Your payment didn't go through") }}</p>
                <p class="text-sm text-ink/60 mb-5">
                    {{ __('Your card was declined or the payment could not be completed. Please try again with a different payment method.') }}
                </p>
                <a href="{{ route('customer.booking.payment', $appointment) }}" class="btn-primary">
                    {{ __('Try Again') }}
                </a>
            </div>
        @else
            <div class="card p-6 text-center mb-6">
                <div class="mx-auto w-12 h-12 rounded-full bg-pine-100 text-pine-700 flex items-center justify-center text-2xl mb-4">&check;</div>

                @if ($appointment->status === 'confirmed')
                    <p class="font-display text-lg text-ink mb-2">{{ __("You're all set!") }}</p>
                    <p class="text-sm text-ink/60">
                        {{ __('Your appointment is confirmed. A receipt and calendar invite have been emailed to you.') }}
                    </p>
                @else
                    <p class="font-display text-lg text-ink mb-2">{{ __('Payment received') }}</p>
                    <p class="text-sm text-ink/60">
                        {{ __("We're finalizing your appointment now — you'll get a confirmation email in just a moment.") }}
                    </p>
                @endif
            </div>

            <div class="card p-6 mb-6">
                <p class="text-xs uppercase tracking-wide text-brass-600 font-medium">{{ $appointment->service->name }}</p>
                <h3 class="font-display text-xl text-ink mt-1">
                    {{ $appointment->appointment_date->toFormattedDateString() }}
                    {{ __('at') }} {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('g:i A') }}
                </h3>
                @if ($appointment->staff)
                    <p class="text-sm text-ink/60 mt-1">{{ __('with :name', ['name' => $appointment->staff->user->name]) }}</p>
                @endif

                <div class="mt-4 pt-4 border-t border-pine-100 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-ink/60">{{ __('Deposit paid') }}</span>
                        <span>${{ number_format($appointment->deposit_paid, 2) }}</span>
                    </div>
                    @if ($appointment->tip_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-ink/60">{{ __('Tip') }}</span>
                            <span>${{ number_format($appointment->tip_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-ink/60">
                        <span>{{ __('Remaining balance (due at checkout)') }}</span>
                        <span>${{ number_format($appointment->remaining_balance, 2) }}</span>
                    </div>
                </div>

                <a
                    href="{{ $appointment->calendarInviteUrl() }}" target="_blank" rel="noopener"
                    class="mt-5 inline-flex items-center gap-1.5 text-sm text-pine-700 hover:underline"
                >
                    {{ __('Add to Google Calendar') }} &rarr;
                </a>
            </div>

            <a href="{{ route('customer.appointments.index') }}" class="btn-primary w-full justify-center">
                {{ __('View My Appointments') }}
            </a>
        @endif
    </div>
</x-app-layout>
