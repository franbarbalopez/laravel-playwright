<?php

namespace FranBarbaLopez\LaravelPlaywright;

use FranBarbaLopez\LaravelPlaywright\Console\Commands\Install;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelPlaywrightServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            return;
        }

        Route::middleware('web')->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/playwright.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
            ]);
        }
    }
}
