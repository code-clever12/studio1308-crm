<?php

use App\Http\Controllers\Api\Admin\FormController as AdminFormController;
use App\Http\Controllers\Api\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FormSubmissionController;
use Illuminate\Support\Facades\Route;

// Public, cross-origin lead-capture endpoint for external static landing
// pages — see config/cors.php for allowed origins. throttle:20,1 is on top
// of (tighter than) the api group's default throttle:api rate limit, since
// an unauthenticated public POST endpoint is a real spam target.
Route::post('/v1/submit-form', [FormSubmissionController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('api.v1.submit-form');

/*
|--------------------------------------------------------------------------
| Mobile App API (Sanctum token auth, admin-only)
|--------------------------------------------------------------------------
| Everything below is for the companion mobile app (lead management only)
| — see docs/MOBILE_API.md. Fully separate from the public submit-form
| endpoint above: authenticated, not CORS-exposed, and never touched by
| external landing pages.
*/
Route::post('/v1/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.v1.auth.login');

Route::middleware(['auth:sanctum', 'role:admin', 'throttle:60,1'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

    Route::post('/device-tokens', [DeviceTokenController::class, 'store'])->name('device-tokens.store');
    Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy'])->name('device-tokens.destroy');

    Route::get('/forms', [AdminFormController::class, 'index'])->name('forms.index');

    Route::get('/submissions/export', [AdminSubmissionController::class, 'export'])->name('submissions.export');
    Route::get('/submissions/services', [AdminSubmissionController::class, 'services'])->name('submissions.services');
    Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [AdminSubmissionController::class, 'show'])->name('submissions.show');
    Route::put('/submissions/{submission}', [AdminSubmissionController::class, 'update'])->name('submissions.update');
    Route::patch('/submissions/{submission}/status', [AdminSubmissionController::class, 'updateStatus'])->name('submissions.update-status');
    Route::delete('/submissions/{submission}', [AdminSubmissionController::class, 'destroy'])->name('submissions.destroy');
});
