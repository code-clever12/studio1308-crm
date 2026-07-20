<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NoShowFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Authorization for this whole controller is handled by the 'role:admin'
 * route middleware — User/customer records have no dedicated Policy.
 */
class CustomerController extends Controller
{
    public function __construct(private readonly NoShowFeeService $noShowFeeService)
    {
    }

    public function index(): View
    {
        return view('admin.customers.index', [
            'customers' => User::where('role', 'customer')
                ->withCount(['appointments'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === 'customer', 404);

        return view('admin.customers.show', [
            'customer' => $customer->load('loyaltyPoints'),
            'appointments' => $customer->appointments()->with(['service', 'staff.user'])->latest('appointment_date')->get(),
            'noShowCount' => $this->noShowFeeService->noShowCountForCustomer($customer),
        ]);
    }

    public function block(User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $this->noShowFeeService->blockCustomer($customer);

        return back()->with('status', 'Customer blocked from booking.');
    }

    public function unblock(User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $this->noShowFeeService->unblockCustomer($customer);

        return back()->with('status', 'Customer unblocked.');
    }
}
