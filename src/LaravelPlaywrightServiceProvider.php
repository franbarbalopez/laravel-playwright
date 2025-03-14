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

        $this->publishes([
            __DIR__.'/../dist/laravel-playwright.es.js' => public_path('vendor/playwright.js'),
            __DIR__.'/../dist/laravel-playwright.umd.js' => public_path('vendor/playwright.umd.js'),
            __DIR__.'/../dist/types/laravel-playwright.d.ts' => public_path('vendor/playwright.d.ts'),
        ], 'laravel-playwright-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
            ]);
        }
    }
}
