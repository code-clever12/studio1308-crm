<x-booking-layout>
    <div
        x-data="bookingWizard({
            services: @js($servicesForJs->values()),
            staff: @js($staffForJs->values()),
            urls: {
                slots: @js(route('customer.booking.slots')),
                breakdown: @js(route('customer.booking.breakdown')),
                store: @js(route('customer.booking.store')),
                waitlist: @js(route('customer.booking.waitlist')),
                appointments: @js(route('customer.appointments.index')),
                payment: @js(route('customer.booking.payment', ['appointment' => '__APPOINTMENT_ID__'])),
            },
            csrf: @js(csrf_token()),
        })"
        @if ($preselectedServiceId) x-init="selectService({{ $preselectedServiceId }})" @endif
    >
        <div x-show="error" x-cloak class="mb-6 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3" x-text="error"></div>

        {{-- Step 1: Service --}}
        <div x-show="step === 1" x-cloak>
            <h1 class="font-display text-2xl text-ink mb-1">{{ __('Choose a service') }}</h1>
            <p class="text-ink/60 mb-6">{{ __("Pick what you're here for today.") }}</p>

            <div class="grid sm:grid-cols-2 gap-4">
                <template x-for="service in services" :key="service.id">
                    <button
                        type="button" @click="selectService(service.id)"
                        class="text-left rounded-xl border bg-white p-5 hover:border-pine-500 transition-colors"
                        :class="serviceId === service.id ? 'border-pine-600 ring-1 ring-pine-600' : 'border-pine-100'"
                    >
                        <p class="text-xs uppercase tracking-wide text-brass-600 font-medium" x-text="service.category"></p>
                        <h3 class="font-display text-lg text-ink mt-1" x-text="service.name"></h3>
                        <p class="text-sm text-ink/60 mt-1" x-text="service.description"></p>
                        <div class="mt-3 flex justify-between text-sm">
                            <span x-text="service.duration_minutes + ' min'"></span>
                            <span class="font-display text-pine-800" x-text="'$' + service.price.toFixed(2)"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Step 2: Staff --}}
        <div x-show="step === 2" x-cloak>
            <button type="button" @click="backTo(1)" class="text-sm text-pine-700 mb-4">&larr; {{ __('Back') }}</button>
            <h1 class="font-display text-2xl text-ink mb-1">{{ __('Choose your barber') }}</h1>
            <p class="text-ink/60 mb-6">{{ __('Or let us pick the first available.') }}</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <button
                    type="button" @click="selectStaff(null)"
                    class="rounded-xl border bg-white p-5 text-center hover:border-pine-500 transition-colors"
                    :class="staffId === null ? 'border-pine-600 ring-1 ring-pine-600' : 'border-pine-100'"
                >
                    <div class="mx-auto w-14 h-14 rounded-full bg-brass-100 text-brass-600 flex items-center justify-center font-display text-lg">?</div>
                    <p class="font-display mt-3 text-sm">{{ __('Any Available') }}</p>
                </button>

                <template x-for="member in eligibleStaff" :key="member.id">
                    <button
                        type="button" @click="selectStaff(member.id)"
                        class="rounded-xl border bg-white p-5 text-center hover:border-pine-500 transition-colors"
                        :class="staffId === member.id ? 'border-pine-600 ring-1 ring-pine-600' : 'border-pine-100'"
                    >
                        <div class="mx-auto w-14 h-14 rounded-full bg-pine-100 text-pine-800 flex items-center justify-center font-display text-lg" x-text="member.name.charAt(0)"></div>
                        <p class="font-display mt-3 text-sm" x-text="member.name"></p>
                        <p class="text-xs text-brass-600" x-show="member.rating">★ <span x-text="member.rating"></span></p>
                    </button>
                </template>

                <p x-show="eligibleStaff.length === 0" class="col-span-full text-sm text-ink/60">
                    {{ __('No barbers currently offer this service — please choose "Any Available" or pick another service.') }}
                </p>
            </div>
        </div>

        {{-- Step 3: Date & time --}}
        <div x-show="step === 3" x-cloak>
            <button type="button" @click="backTo(2)" class="text-sm text-pine-700 mb-4">&larr; {{ __('Back') }}</button>
            <h1 class="font-display text-2xl text-ink mb-1">{{ __('Pick a date & time') }}</h1>
            <p class="text-ink/60 mb-6" x-text="selectedStaff ? 'with ' + selectedStaff.name : 'with the first available barber'"></p>

            <input
                type="date" x-model="date" @change="loadSlots()"
                :min="new Date().toISOString().split('T')[0]"
                class="rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500 mb-6"
            >

            <div x-show="loadingSlots" class="text-sm text-ink/60">{{ __('Loading times…') }}</div>

            <div x-show="!loadingSlots && date">
                <x-time-slot-picker />

                <div x-show="availableTimes.length === 0 && date && !loadingSlots" class="mt-4 text-sm">
                    <p class="text-ink/60 mb-2">{{ __('No times available on this date.') }}</p>
                    <button type="button" @click="joinWaitlist()" x-show="!joinedWaitlist" class="text-pine-700 underline">
                        {{ __('Join the waitlist for this date instead') }}
                    </button>
                    <p x-show="joinedWaitlist" class="text-pine-700 font-medium">{{ __("You're on the waitlist!") }}</p>
                </div>
            </div>

            <button
                type="button" @click="goToDetails()" x-show="startTime"
                class="mt-6 inline-flex items-center px-5 py-2.5 rounded-md bg-pine-800 text-parchment text-sm font-medium hover:bg-pine-700 transition-colors"
            >
                {{ __('Continue') }}
            </button>
        </div>

        {{-- Step 4: Consent form --}}
        <div x-show="step === 4" x-cloak>
            <button type="button" @click="backTo(3)" class="text-sm text-pine-700 mb-4">&larr; {{ __('Back') }}</button>
            <h1 class="font-display text-2xl text-ink mb-1" x-text="selectedService?.consent_form?.name"></h1>
            <p class="text-ink/60 mb-6">{{ __('Please complete this before your appointment.') }}</p>

            <template x-if="selectedService?.consent_form">
                <div class="space-y-4">
                    <template x-for="field in selectedService.consent_form.fields" :key="field.id">
                        <div class="bg-white border border-pine-100 rounded-lg p-4">
                            <label class="block text-sm font-medium text-ink mb-2" x-text="field.label + (field.required ? ' *' : '')"></label>

                            <template x-if="field.type === 'checkbox'">
                                <input type="checkbox" x-model="formResponses[field.id]" class="rounded border-pine-300 text-pine-700">
                            </template>

                            <template x-if="field.type === 'radio'">
                                <div class="space-y-1">
                                    <template x-for="option in (field.options || [])" :key="option">
                                        <label class="flex items-center gap-2 text-sm text-ink/80">
                                            <input type="radio" :name="field.id" :value="option" x-model="formResponses[field.id]" class="border-pine-300 text-pine-700">
                                            <span x-text="option"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!['checkbox', 'radio'].includes(field.type)">
                                <input :type="field.type" x-model="formResponses[field.id]" class="w-full rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500">
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <button
                type="button" @click="goToReview()"
                class="mt-6 inline-flex items-center px-5 py-2.5 rounded-md bg-pine-800 text-parchment text-sm font-medium hover:bg-pine-700 transition-colors"
            >
                {{ __('Continue') }}
            </button>
        </div>

        {{-- Step 5: Review & confirm --}}
        <div x-show="step === 5" x-cloak>
            <button type="button" @click="backTo(selectedService?.requires_consent_form ? 4 : 3)" class="text-sm text-pine-700 mb-4">&larr; {{ __('Back') }}</button>
            <h1 class="font-display text-2xl text-ink mb-4">{{ __('Review & confirm') }}</h1>

            <div class="bg-white border border-pine-100 rounded-xl p-6 text-sm" x-show="breakdown">
                <div class="flex justify-between py-1">
                    <span>{{ __('Service') }}</span>
                    <span x-text="'$' + breakdown?.subtotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between py-1 text-ink/60" x-show="breakdown?.tax > 0">
                    <span>{{ __('Sales Tax') }}</span>
                    <span x-text="'+$' + breakdown?.tax.toFixed(2)"></span>
                </div>
                <div class="flex justify-between py-2 font-medium border-t border-pine-100 mt-1">
                    <span>{{ __('Subtotal') }}</span>
                    <span x-text="'$' + breakdown?.total_amount.toFixed(2)"></span>
                </div>
                <div class="flex justify-between py-1 text-pine-800 font-medium">
                    <span>{{ __('Deposit (due today)') }}</span>
                    <span x-text="'$' + breakdown?.deposit.toFixed(2)"></span>
                </div>

                <div class="pt-4 mt-2 border-t border-pine-100">
                    <p class="font-medium text-ink mb-2">{{ __('Add a tip for your barber') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="pct in [15, 18, 20]" :key="pct">
                            <button
                                type="button" @click="tipPercent = pct"
                                class="px-3 py-1.5 rounded-md border text-sm transition-colors"
                                :class="tipPercent === pct ? 'bg-brass-500 border-brass-500 text-pine-950' : 'border-pine-200 text-ink/80'"
                            >
                                <span x-text="pct + '%'"></span>
                            </button>
                        </template>
                        <button
                            type="button" @click="tipPercent = 'custom'"
                            class="px-3 py-1.5 rounded-md border text-sm transition-colors"
                            :class="tipPercent === 'custom' ? 'bg-brass-500 border-brass-500 text-pine-950' : 'border-pine-200 text-ink/80'"
                        >
                            {{ __('Custom') }}
                        </button>
                        <button
                            type="button" @click="tipPercent = null"
                            class="px-3 py-1.5 rounded-md border text-sm transition-colors"
                            :class="tipPercent === null ? 'bg-brass-500 border-brass-500 text-pine-950' : 'border-pine-200 text-ink/80'"
                        >
                            {{ __('No tip') }}
                        </button>
                    </div>
                    <input
                        x-show="tipPercent === 'custom'" x-cloak type="number" x-model="customTip" min="0" step="1"
                        placeholder="$0.00" class="mt-2 w-32 rounded-md border-pine-200 text-sm focus:border-pine-500 focus:ring-pine-500"
                    >
                </div>

                <div class="flex justify-between font-display text-lg border-t border-pine-100 pt-3 mt-3">
                    <span>{{ __('Total due today') }}</span>
                    <span x-text="'$' + (breakdown ? (breakdown.deposit + tipAmount).toFixed(2) : '0.00')"></span>
                </div>
            </div>

            <button
                type="button" @click="submitBooking()" :disabled="submitting"
                class="mt-6 inline-flex items-center px-6 py-3 rounded-md bg-pine-800 text-parchment font-medium hover:bg-pine-700 disabled:opacity-50 transition-colors"
            >
                <span x-show="!submitting">{{ __('Continue to Payment') }}</span>
                <span x-show="submitting" x-cloak>{{ __('Booking…') }}</span>
            </button>
        </div>
    </div>
</x-booking-layout>
