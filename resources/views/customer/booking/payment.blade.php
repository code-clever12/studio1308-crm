<x-app-layout>
    <x-slot name="header">
        <p class="font-display text-sm uppercase tracking-widest text-brass-400">{{ __('Almost done') }}</p>
        <h2 class="font-display text-3xl mt-1">{{ __('Secure Your Appointment') }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
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
                    <span class="text-ink/60">{{ __('Service total') }}</span>
                    <span>${{ number_format($breakdown['total_amount'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink/60">{{ __('Deposit due today') }}</span>
                    <span>${{ number_format($breakdown['deposit'], 2) }}</span>
                </div>
                @if ($breakdown['tip'] > 0)
                    <div class="flex justify-between">
                        <span class="text-ink/60">{{ __('Tip') }}</span>
                        <span>${{ number_format($breakdown['tip'], 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-display text-lg text-ink border-t border-pine-100 pt-2 mt-2">
                    <span>{{ __('Total due today') }}</span>
                    <span>${{ number_format($breakdown['charge_today'], 2) }}</span>
                </div>
                <p class="text-xs text-ink/50 pt-1">
                    {{ __('Remaining balance of :amount is due at checkout.', ['amount' => '$' . number_format($breakdown['remaining_balance'], 2)]) }}
                </p>
            </div>
        </div>

        @if ($stripeConfigured)
            <div class="card p-6" x-data="stripePayment(@js([
                'stripeKey' => $stripeKey,
                'clientSecret' => $clientSecret,
                'returnUrl' => route('customer.booking.confirmation', $appointment),
            ]))" x-init="init()">
                <div x-show="error" x-cloak class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3" x-text="error"></div>

                <div id="payment-element" class="mb-5"></div>

                <button
                    type="button" @click="pay()" :disabled="submitting || !ready"
                    class="w-full inline-flex items-center justify-center px-6 py-3 rounded-md bg-pine-800 text-parchment font-medium hover:bg-pine-700 disabled:opacity-50 transition-colors"
                >
                    <span x-show="!submitting">{{ __('Pay $:amount', ['amount' => number_format($breakdown['charge_today'], 2)]) }}</span>
                    <span x-show="submitting" x-cloak>{{ __('Processing…') }}</span>
                </button>

                <p class="text-xs text-ink/50 mt-3 text-center">
                    {{ __('Your card is saved securely with Stripe for no-show fee protection, per our cancellation policy.') }}
                </p>
            </div>
        @else
            <div class="card p-6 text-center">
                <p class="font-display text-lg text-ink mb-2">{{ __('Online payment isn\'t connected yet') }}</p>
                <p class="text-sm text-ink/60 mb-5">
                    {{ __("We'll hold your appointment request and collect the deposit in person at check-in. You'll receive a confirmation email shortly.") }}
                </p>
                <a href="{{ route('customer.appointments.index') }}" class="btn-primary">
                    {{ __('View My Appointments') }}
                </a>
            </div>
        @endif
    </div>

    @if ($stripeConfigured)
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('stripePayment', (config) => ({
                    stripe: null,
                    elements: null,
                    ready: false,
                    submitting: false,
                    error: null,

                    init() {
                        this.stripe = Stripe(config.stripeKey);
                        this.elements = this.stripe.elements({ clientSecret: config.clientSecret });

                        const paymentElement = this.elements.create('payment');
                        paymentElement.mount('#payment-element');
                        paymentElement.on('ready', () => { this.ready = true; });
                    },

                    async pay() {
                        this.submitting = true;
                        this.error = null;

                        const { error } = await this.stripe.confirmPayment({
                            elements: this.elements,
                            confirmParams: { return_url: config.returnUrl },
                        });

                        if (error) {
                            this.error = error.message ?? 'Something went wrong processing your payment. Please try again.';
                            this.submitting = false;
                        }
                    },
                }));
            });
        </script>
    @endif
</x-app-layout>
