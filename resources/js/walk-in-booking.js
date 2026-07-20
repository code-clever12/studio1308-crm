export default function walkInBooking(config) {
    return {
        services: config.services,
        staffList: config.staff,
        urls: config.urls,

        serviceId: '',
        staffId: '',
        date: '',
        startTime: '',

        slotsByStaff: {},
        loadingSlots: false,

        get selectedService() {
            return this.services.find((s) => s.id === Number(this.serviceId)) ?? null;
        },

        get eligibleStaff() {
            if (!this.serviceId) return this.staffList;
            return this.staffList.filter((s) => s.service_ids.includes(Number(this.serviceId)));
        },

        get availableTimes() {
            const times = new Set();
            Object.values(this.slotsByStaff).forEach((list) => list.forEach((t) => times.add(t)));
            return Array.from(times).sort();
        },

        async loadSlots() {
            if (!this.serviceId || !this.date) return;

            this.loadingSlots = true;
            this.startTime = '';
            this.slotsByStaff = {};

            const params = new URLSearchParams({ service_id: this.serviceId, date: this.date });
            if (this.staffId) params.set('staff_id', this.staffId);

            try {
                const response = await fetch(`${this.urls.slots}?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();
                this.slotsByStaff = data.slots ?? {};
            } finally {
                this.loadingSlots = false;
            }
        },

        selectTime(time) {
            this.startTime = time;
        },
    };
}
