<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FormBuilderController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Customer\AppointmentController as CustomerAppointmentController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\BrowseController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\TipController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $user = $request->user();

    if (! $user->isCustomer()) {
        return view('dashboard');
    }

    $upcoming = $user->appointments()
        ->with(['service', 'staff.user'])
        ->whereIn('status', ['pending', 'confirmed'])
        ->orderBy('appointment_date')
        ->orderBy('start_time')
        ->take(3)
        ->get();

    return view('dashboard', [
        'upcoming' => $upcoming,
        'loyaltyPoints' => $user->loyaltyPoints,
        'waitlistCount' => $user->waitlistEntries()->where('status', 'waiting')->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/browse', [BrowseController::class, 'index'])->name('browse');

    Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::get('/booking/slots', [BookingController::class, 'slots'])->name('booking.slots');
    Route::get('/booking/breakdown', [BookingController::class, 'breakdown'])->name('booking.breakdown');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{appointment}/payment', [BookingController::class, 'payment'])->name('booking.payment');
    Route::get('/booking/{appointment}/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
    Route::post('/booking/waitlist', [BookingController::class, 'joinWaitlist'])->name('booking.waitlist');

    Route::get('/appointments', [CustomerAppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/appointments/{appointment}', [CustomerAppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [CustomerAppointmentController::class, 'destroy'])->name('appointments.destroy');

    Route::post('/appointments/{appointment}/review', [ReviewController::class, 'store'])->name('appointments.review');

    Route::get('/tips', [TipController::class, 'index'])->name('tips.index');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('appointments', AdminAppointmentController::class)->except(['show', 'edit']);
    Route::get('/appointments-slots', [AdminAppointmentController::class, 'slots'])->name('appointments.slots');

    Route::resource('staff', StaffController::class)->except(['show']);
    Route::put('/staff/{staff}/ach-account', [StaffController::class, 'updateAchAccount'])->name('staff.ach-account.update');
    Route::post('/staff/{staff}/ach-account/verify', [StaffController::class, 'verifyAchAccount'])->name('staff.ach-account.verify');
    Route::get('/staff/{staff}/schedule', [ScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/staff/{staff}/schedule', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::post('/staff/{staff}/days-off', [ScheduleController::class, 'storeDayOff'])->name('schedules.days-off.store');
    Route::delete('/staff/{staff}/days-off/{dayOff}', [ScheduleController::class, 'destroyDayOff'])->name('schedules.days-off.destroy');

    Route::resource('services', AdminServiceController::class)->except(['show']);

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers/{customer}/block', [AdminCustomerController::class, 'block'])->name('customers.block');
    Route::post('/customers/{customer}/unblock', [AdminCustomerController::class, 'unblock'])->name('customers.unblock');

    Route::resource('forms', FormBuilderController::class)->except(['show']);
    Route::get('/forms/{form}/responses', [FormBuilderController::class, 'responses'])->name('forms.responses');

    Route::get('/reports/commission', [ReportController::class, 'commission'])->name('reports.commission');
    Route::get('/reports/tips', [ReportController::class, 'tips'])->name('reports.tips');
    Route::get('/reports/tax', [ReportController::class, 'tax'])->name('reports.tax');

    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
    Route::post('/payouts/staff/{staff}', [PayoutController::class, 'storeForStaff'])->name('payouts.store-for-staff');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::post('/webhooks/stripe', [PaymentController::class, 'webhook'])->name('webhooks.stripe');
Route::post('/webhooks/stripe/payouts', [PaymentController::class, 'payoutWebhook'])->name('webhooks.stripe.payouts');

require __DIR__.'/auth.php';
