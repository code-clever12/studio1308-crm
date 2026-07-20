<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

/**
 * Authorization is handled by the 'role:admin' route middleware.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $todaysAppointments = Appointment::whereDate('appointment_date', $today)->get();

        $topServices = Service::withCount(['appointments' => function ($query) {
            $query->whereMonth('appointment_date', now()->month)
                ->whereYear('appointment_date', now()->year);
        }])
            ->orderByDesc('appointments_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                'todays_appointments' => $todaysAppointments->count(),
                'todays_revenue' => round((float) $todaysAppointments->whereIn('status', ['completed', 'confirmed'])->sum('deposit_paid'), 2),
                'active_customers' => User::where('role', 'customer')->where('is_active', true)->count(),
                'upcoming_appointments' => Appointment::whereBetween('appointment_date', [$today, $today->copy()->addDays(7)])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count(),
            ],
            'topServices' => $topServices,
        ]);
    }
}
