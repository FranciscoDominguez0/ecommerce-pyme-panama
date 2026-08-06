<?php

namespace App\Providers;

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
        if ($this->app->runningInConsole()) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
        } elseif ($this->app->environment('local')) {
            @ini_set('max_execution_time', '120');
        }
    }
}
