<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        RateLimiter::for('fortify-login', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));
    }
}
