<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Salon Settings') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl card">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Salon Info') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $salon->name) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Website') }}</label>
                        <input type="url" name="website" value="{{ old('website', $salon->website) }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $salon->description) }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $salon->phone) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $salon->email) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Lead Notifications') }}</h3>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Additional Notification Emails') }}</label>
                <textarea name="lead_notification_emails" rows="2" placeholder="{{ __('owner@example.com, sales@example.com') }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('lead_notification_emails', $salon->lead_notification_emails) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">{{ __('Comma-separated. These addresses get emailed every time a new lead comes in through a landing page form, in addition to admin accounts.') }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Address & Timezone') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Street Address') }}</label>
                        <input type="text" name="address" value="{{ old('address', $salon->address) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('City') }}</label>
                        <input type="text" name="city" value="{{ old('city', $salon->city) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('State') }}</label>
                            <input type="text" name="state" value="{{ old('state', $salon->state) }}" maxlength="2" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('ZIP') }}</label>
                            <input type="text" name="zip_code" value="{{ old('zip_code', $salon->zip_code) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Timezone') }}</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $salon->timezone) }}" required class="w-full sm:w-64 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Hours & Cancellation Policy') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Opens At') }}</label>
                        <input type="time" name="opens_at" value="{{ old('opens_at', \Illuminate\Support\Carbon::parse($salon->opens_at)->format('H:i')) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Closes At') }}</label>
                        <input type="time" name="closes_at" value="{{ old('closes_at', \Illuminate\Support\Carbon::parse($salon->closes_at)->format('H:i')) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cancellation Policy (displayed to customers)') }}</label>
                    <textarea name="cancellation_policy" rows="2" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('cancellation_policy', $salon->cancellation_policy) }}</textarea>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Payments, Deposits & Tax') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deposit %') }}</label>
                        <input type="number" name="deposit_percentage" value="{{ old('deposit_percentage', $salon->deposit_percentage) }}" min="0" max="100" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('No-Show Fee ($)') }}</label>
                        <input type="number" name="no_show_fee" value="{{ old('no_show_fee', $salon->no_show_fee) }}" min="0" step="0.01" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sales Tax Rate (%)') }}</label>
                        <input type="number" name="sales_tax_rate" value="{{ old('sales_tax_rate', $salon->sales_tax_rate) }}" min="0" max="100" step="0.001" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-4">
                    <input type="hidden" name="enable_tips" value="0">
                    <input type="checkbox" name="enable_tips" value="1" @checked(old('enable_tips', $salon->enable_tips)) class="rounded border-gray-300 text-indigo-600">
                    {{ __('Enable tipping at checkout (15% / 18% / 20% / custom)') }}
                </label>
            </div>

            <button type="submit" class="btn-primary">
                {{ __('Save Settings') }}
            </button>
        </form>
    </div>
</x-admin-layout>
