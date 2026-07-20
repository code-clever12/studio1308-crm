<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit :name', ['name' => $staff->user->name]) }}
        </h2>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="card">
            <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $staff->user->name) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $staff->user->email) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->user->phone) }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bio') }}</label>
                    <textarea name="bio" rows="3" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('bio', $staff->bio) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Commission %') }}</label>
                        <input type="number" name="commission_rate" value="{{ old('commission_rate', $staff->commission_rate) }}" min="0" max="100" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                        <select name="status" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $staff->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Hire Date') }}</label>
                        <input type="date" name="hire_date" value="{{ old('hire_date', $staff->hire_date?->toDateString()) }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Services Offered') }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($services as $service)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked($staff->services->contains('id', $service->id)) class="rounded border-gray-300 text-indigo-600">
                                {{ $service->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="btn-primary">
                        {{ __('Save Changes') }}
                    </button>

                    @if ($staff->status !== 'inactive')
                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'deactivate-staff')" class="text-sm text-red-600 hover:underline">
                            {{ __('Deactivate Staff Member') }}
                        </button>

                        <x-confirm-modal
                            id="deactivate-staff"
                            :title="__('Deactivate :name?', ['name' => $staff->user->name])"
                            :action="route('admin.staff.destroy', $staff)"
                            :confirm-label="__('Deactivate')"
                        >
                            {{ __("They won't be bookable for new appointments and their account will be disabled. Existing appointments are unaffected.") }}
                        </x-confirm-modal>
                    @endif
                </div>
            </form>
        </div>

        <div class="card">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('ACH Bank Account (for Payouts)') }}</h3>
            <p class="text-xs text-gray-500 mb-4">
                {{ __('Used to pay out commission and tips. Stripe Connect verification is implemented in Step 8 — accounts save as "pending" until then.') }}
            </p>

            @if ($staff->achBankAccount)
                <div class="flex items-center gap-3 mb-4 text-sm">
                    <x-status-badge :status="$staff->achBankAccount->verification_status" />
                    <span class="text-gray-600">
                        {{ $staff->achBankAccount->bank_name ?? __('Bank') }} &middot; ****{{ $staff->achBankAccount->last_4_digits }}
                    </span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.staff.ach-account.update', $staff) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account Holder Name') }}</label>
                        <input type="text" name="bank_account_holder_name" value="{{ old('bank_account_holder_name', $staff->achBankAccount?->bank_account_holder_name) }}" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bank Name') }}</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $staff->achBankAccount?->bank_name) }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Routing Number') }}</label>
                        <input type="text" name="bank_account_routing_number" inputmode="numeric" maxlength="9" placeholder="•••••••••" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account Number') }}</label>
                        <input type="text" name="bank_account_number" inputmode="numeric" placeholder="••••••••" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-md bg-gray-800 text-white text-sm font-medium hover:bg-gray-700">
                    {{ __('Save Bank Account') }}
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
