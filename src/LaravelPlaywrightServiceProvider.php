<?php

namespace FranBarbaLopez\LaravelPlaywright;

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

        $this->loadRoutesFrom(__DIR__.'/../routes/playwright.php');
    }
}
