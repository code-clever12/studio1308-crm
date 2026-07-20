<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessACHPayouts;
use App\Models\ACHPayout;
use App\Models\Staff;
use App\Services\ACHPayoutService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Authorization is handled by the 'role:admin' route middleware.
 */
class PayoutController extends Controller
{
    public function __construct(private readonly ACHPayoutService $achPayoutService)
    {
    }

    public function index(): View
    {
        return view('admin.payouts.index', [
            'payouts' => ACHPayout::with('staff.user')->latest()->get(),
        ]);
    }

    /**
     * Trigger a batch payout run for all eligible staff over a date range.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        ProcessACHPayouts::dispatch(Carbon::parse($data['from'])->startOfDay(), Carbon::parse($data['to'])->endOfDay());

        return redirect()->route('admin.payouts.index')->with('status', 'Payout run queued.');
    }

    /**
     * Create (and attempt to initiate) a payout for a single staff member.
     */
    public function storeForStaff(Request $request, Staff $staff): RedirectResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $payout = $this->achPayoutService->createPayout(
            $staff,
            Carbon::parse($data['from'])->startOfDay(),
            Carbon::parse($data['to'])->endOfDay(),
        );

        return redirect()->route('admin.payouts.index')->with('status', "Payout of \${$payout->amount} created for {$staff->user->name}.");
    }
}
