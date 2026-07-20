<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Walk-in Booking') }}
        </h2>
    </x-slot>

    <div
        x-data="walkInBooking({
            services: @js($services->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->price, 'duration_minutes' => $s->duration_minutes])),
            staff: @js($staff->map(fn ($s) => ['id' => $s->id, 'name' => $s->user->name, 'service_ids' => $s->services->pluck('id')])),
            urls: { slots: @js(route('admin.appointments.slots')) },
        })"
        class="max-w-2xl card"
    >
        <form method="POST" action="{{ route('admin.appointments.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Customer') }}</label>
                <select name="customer_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Select a customer…') }}</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Service') }}</label>
                <select name="service_id" x-model="serviceId" @change="staffId = ''; loadSlots()" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Select a service…') }}</option>
                    <template x-for="service in services" :key="service.id">
                        <option :value="service.id" x-text="service.name + ' — $' + service.price.toFixed(2) + ' (' + service.duration_minutes + ' min)'"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Staff') }}</label>
                <select name="staff_id" x-model="staffId" @change="loadSlots()" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Any available') }}</option>
                    <template x-for="member in eligibleStaff" :key="member.id">
                        <option :value="member.id" x-text="member.name"></option>
                    </template>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }}</label>
                    <input type="date" name="appointment_date" x-model="date" @change="loadSlots()" min="{{ now()->toDateString() }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tip (optional)') }}</label>
                    <input type="number" name="tip_amount" min="0" step="0.01" placeholder="$0.00" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Time') }}</label>
                <p x-show="loadingSlots" class="text-sm text-gray-500">{{ __('Loading available times…') }}</p>
                <div x-show="!loadingSlots && serviceId && date">
                    <x-time-slot-picker />
                </div>
                <input type="hidden" name="start_time" :value="startTime" required>
            </div>

            <button
                type="submit" :disabled="!startTime"
                class="btn-primary"
            >
                {{ __('Book Appointment') }}
            </button>
        </form>
    </div>
</x-admin-layout>
