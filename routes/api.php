<?php

use App\Http\Controllers\Api\FormSubmissionController;
use Illuminate\Support\Facades\Route;

// Public, cross-origin lead-capture endpoint for external static landing
// pages — see config/cors.php for allowed origins. throttle:20,1 is on top
// of (tighter than) the api group's default throttle:api rate limit, since
// an unauthenticated public POST endpoint is a real spam target.
Route::post('/v1/submit-form', [FormSubmissionController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('api.v1.submit-form');
