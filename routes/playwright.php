<?php

use FranBarbaLopez\LaravelPlaywright\Http\Controllers\PlaywrightController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('__playwright__/csrf_token', [PlaywrightController::class, 'csrfToken'])->name('playwright.csrf_token');
});
