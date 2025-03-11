<?php

namespace FranBarbaLopez\LaravelPlaywright;

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

        // TODO: Publish the Javascript Compiled from Typescript helper
    }
}
