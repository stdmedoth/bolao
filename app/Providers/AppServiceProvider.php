<?php

namespace App\Providers;

use App\Models\ReferEarn;
use App\Observers\ReferEarnObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observer para ReferEarn
        ReferEarn::observe(ReferEarnObserver::class);
    }
}
