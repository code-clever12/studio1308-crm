<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Tip;
use App\Services\CommissionService;
use App\Services\SalesTaxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Authorization is handled by the 'role:admin' route middleware.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly SalesTaxService $salesTaxService,
    ) {
    }

    public function commission(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $reports = Staff::with('user')->get()->map(
            fn (Staff $staff) => $this->commissionService->commissionReport($staff, $from, $to)
                + ['staff_name' => $staff->user->name]
        );

        return view('admin.reports.commission', compact('reports', 'from', 'to'));
    }

    public function tips(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $tipsByStaff = Tip::with('staff.user')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy('staff_id')
            ->map(fn ($tips) => [
                'staff_name' => $tips->first()->staff->user->name,
                'total_tips' => round((float) $tips->sum('amount'), 2),
                'tip_count' => $tips->count(),
            ]);

        return view('admin.reports.tips', compact('tipsByStaff', 'from', 'to'));
    }

    public function tax(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $report = $this->salesTaxService->taxReport($from, $to);

        return view('admin.reports.tax', compact('report', 'from', 'to'));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();

        return [Carbon::instance($from)->startOfDay(), Carbon::instance($to)->endOfDay()];
    }
}
