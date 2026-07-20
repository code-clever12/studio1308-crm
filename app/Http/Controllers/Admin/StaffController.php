<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\ACHBankAccount;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Staff::class);

        return view('admin.staff.index', [
            'staff' => Staff::with('user')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Staff::class);

        return view('admin.staff.create', ['services' => Service::orderBy('name')->get()]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $this->authorize('create', Staff::class);

        $staff = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'password' => Hash::make(Str::random(32)),
                'role' => 'staff',
                'is_active' => true,
            ]);

            $staff = Staff::create([
                'user_id' => $user->id,
                'bio' => $request->validated('bio'),
                'commission_rate' => $request->validated('commission_rate'),
                'status' => $request->validated('status'),
                'hire_date' => $request->validated('hire_date'),
            ]);

            $staff->services()->sync(
                collect($request->validated('service_ids', []))
                    ->mapWithKeys(fn ($id) => [$id => ['is_available' => true]])
            );

            return $staff;
        });

        return redirect()->route('admin.staff.edit', $staff)->with('status', 'Staff member added.');
    }

    public function edit(Staff $staff): View
    {
        $this->authorize('update', $staff);

        return view('admin.staff.edit', [
            'staff' => $staff->load('user', 'services', 'schedules', 'achBankAccount'),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function update(StaffRequest $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);

        DB::transaction(function () use ($request, $staff) {
            $staff->user->update([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
            ]);

            $staff->update([
                'bio' => $request->validated('bio'),
                'commission_rate' => $request->validated('commission_rate'),
                'status' => $request->validated('status'),
                'hire_date' => $request->validated('hire_date'),
            ]);

            $staff->services()->sync(
                collect($request->validated('service_ids', []))
                    ->mapWithKeys(fn ($id) => [$id => ['is_available' => true]])
            );
        });

        return redirect()->route('admin.staff.edit', $staff)->with('status', 'Staff member updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);

        DB::transaction(function () use ($staff) {
            $staff->user->update(['is_active' => false]);
            $staff->update(['status' => 'inactive']);
        });

        return redirect()->route('admin.staff.index')->with('status', 'Staff member deactivated.');
    }

    /**
     * Add or replace a staff member's ACH bank account on file for payouts.
     */
    public function updateAchAccount(Request $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);

        $data = $request->validate([
            'bank_account_holder_name' => ['required', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_routing_number' => ['required', 'digits:9'],
            'bank_account_number' => ['required', 'digits_between:4,17'],
        ]);

        ACHBankAccount::updateOrCreate(
            ['staff_id' => $staff->id],
            [
                'bank_account_holder_name' => $data['bank_account_holder_name'],
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_routing_number' => $data['bank_account_routing_number'],
                'bank_account_number' => $data['bank_account_number'],
                'last_4_digits' => substr($data['bank_account_number'], -4),
                'verification_status' => 'pending',
            ]
        );

        return redirect()->route('admin.staff.edit', $staff)->with('status', 'Bank account saved. Verification is pending Stripe Connect integration (Step 8).');
    }
}
