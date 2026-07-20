export default function bookingWizard(config) {
    return {
        step: 1,
        stepLabels: ['Service', 'Staff', 'Date & Time', 'Details', 'Review'],

        services: config.services,
        staffList: config.staff,
        urls: config.urls,
        csrf: config.csrf,

        serviceId: null,
        staffId: null,
        date: '',
        startTime: '',
        formResponses: {},
        tipPercent: null,
        customTip: '',

        slotsByStaff: {},
        breakdown: null,
        loadingSlots: false,
        loadingBreakdown: false,
        submitting: false,
        error: null,
        fieldErrors: {},
        joinedWaitlist: false,

        get selectedService() {
            return this.services.find((s) => s.id === this.serviceId) ?? null;
        },

        get selectedStaff() {
            return this.staffList.find((s) => s.id === this.staffId) ?? null;
        },

        get eligibleStaff() {
            if (!this.selectedService) return [];
            return this.staffList.filter((s) => s.service_ids.includes(this.serviceId));
        },

        get availableTimes() {
            const times = new Set();
            Object.values(this.slotsByStaff).forEach((list) => list.forEach((t) => times.add(t)));
            return Array.from(times).sort();
        },

        get tipAmount() {
            if (!this.breakdown) return 0;
            if (this.tipPercent === 'custom') {
                return Number(this.customTip || 0);
            }
            if (this.tipPercent) {
                return Math.round(this.breakdown.subtotal * (this.tipPercent / 100) * 100) / 100;
            }
            return 0;
        },

        selectService(id) {
            this.serviceId = id;
            this.staffId = null;
            this.error = null;
            this.step = 2;
        },

        selectStaff(id) {
            this.staffId = id;
            this.step = 3;
            this.loadSlots();
        },

        async loadSlots() {
            if (!this.serviceId || !this.date) return;

            this.loadingSlots = true;
            this.slotsByStaff = {};
            this.startTime = '';

            const params = new URLSearchParams({ service_id: this.serviceId, date: this.date });
            if (this.staffId) params.set('staff_id', this.staffId);

            try {
                const response = await fetch(`${this.urls.slots}?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();
                this.slotsByStaff = data.slots ?? {};
            } catch (e) {
                this.error = 'Could not load available times. Please try again.';
            } finally {
                this.loadingSlots = false;
            }
        },

        selectTime(time) {
            this.startTime = time;
        },

        async goToDetails() {
            if (!this.startTime) return;

            if (this.selectedService?.requires_consent_form) {
                this.step = 4;
            } else {
                this.step = 5;
                await this.loadBreakdown();
            }
        },

        async goToReview() {
            this.step = 5;
            await this.loadBreakdown();
        },

        async loadBreakdown() {
            this.loadingBreakdown = true;

            try {
                const response = await fetch(`${this.urls.breakdown}?service_id=${this.serviceId}`, {
                    headers: { Accept: 'application/json' },
                });
                this.breakdown = await response.json();
            } catch (e) {
                this.error = 'Could not calculate pricing. Please try again.';
            } finally {
                this.loadingBreakdown = false;
            }
        },

        async submitBooking() {
            this.submitting = true;
            this.error = null;
            this.fieldErrors = {};

            const payload = {
                service_id: this.serviceId,
                staff_id: this.staffId,
                appointment_date: this.date,
                start_time: this.startTime,
            };

            if (this.selectedService?.requires_consent_form && this.selectedService?.consent_form) {
                payload.form_responses = {
                    [this.selectedService.consent_form.id]: this.formResponses,
                };
            }

            try {
                const response = await fetch(this.urls.store, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify(payload),
                });

                if (response.status === 422) {
                    const data = await response.json();
                    this.fieldErrors = data.errors ?? {};
                    this.error = data.message ?? 'Please correct the highlighted fields.';
                    return;
                }

                if (!response.ok) {
                    this.error = 'Something went wrong booking your appointment. Please try again.';
                    return;
                }

                window.location.href = this.urls.appointments;
            } catch (e) {
                this.error = 'Something went wrong booking your appointment. Please try again.';
            } finally {
                this.submitting = false;
            }
        },

        async joinWaitlist() {
            this.submitting = true;
            this.error = null;

            try {
                const response = await fetch(this.urls.waitlist, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify({
                        service_id: this.serviceId,
                        staff_id: this.staffId,
                        requested_date: this.date,
                    }),
                });

                if (response.ok) {
                    this.joinedWaitlist = true;
                } else {
                    this.error = 'Could not join the waitlist. Please try again.';
                }
            } catch (e) {
                this.error = 'Could not join the waitlist. Please try again.';
            } finally {
                this.submitting = false;
            }
        },

        backTo(step) {
            this.error = null;
            this.step = step;
        },
    };
}
