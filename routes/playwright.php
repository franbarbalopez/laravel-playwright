<?php

use FranBarbaLopez\LaravelPlaywright\Http\Controllers\PlaywrightController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('__playwright__/csrf_token', [PlaywrightController::class, 'csrfToken'])->name('playwright.csrf_token');
    Route::post('__playwright__/factory', [PlaywrightController::class, 'factory'])->name('playwright.factory');
    Route::post('__playwright__/login', [PlaywrightController::class, 'login'])->name('playwright.login');
    Route::post('__playwright__/logout', [PlaywrightController::class, 'logout'])->name('playwright.logout');
    Route::get('__playwright__/user', [PlaywrightController::class, 'user'])->name('playwright.user');
});
