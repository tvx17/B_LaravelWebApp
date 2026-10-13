<?php

use App\Actions\Auth\SendEmailVerificationAction;
use App\Actions\Auth\VerificationStatusAction;
use App\Actions\Auth\VerifyEmailAction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

// Session-based JSON endpoints deliberately retain web middleware and CSRF protection.
Route::prefix('api/email-verification')->middleware('auth')->group(function () {
    Route::get('/status', VerificationStatusAction::class)->name('api.verification.status');
    Route::post('/notification', SendEmailVerificationAction::class)
        ->middleware('throttle:6,1')->name('api.verification.send');
    Route::get('/verify/{id}/{hash}', VerifyEmailAction::class)
        ->middleware(['signed', 'throttle:6,1'])->name('api.verification.verify');
});
