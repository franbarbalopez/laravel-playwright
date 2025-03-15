<?php

namespace FranBarbaLopez\LaravelPlaywright\Tests;

use FranBarbaLopez\LaravelPlaywright\Http\Controllers\PlaywrightController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;

#[WithEnv('DB_CONNECTION', 'testing')]
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            'FranBarbaLopez\LaravelPlaywright\LaravelPlaywrightServiceProvider',
        ];
    }

    /**
     * Define routes setup.
     *
     * @param  Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router->group(['middleware' => 'web'], function ($router) {
            $router->get('__playwright__/csrf-token', [PlaywrightController::class, 'csrfToken'])->name('playwright.csrf-token');
            $router->post('__playwright__/factory', [PlaywrightController::class, 'factory'])->name('playwright.factory');
            $router->post('__playwright__/login', [PlaywrightController::class, 'login'])->name('playwright.login');
            $router->post('__playwright__/logout', [PlaywrightController::class, 'logout'])->name('playwright.logout');
            $router->get('__playwright__/user', [PlaywrightController::class, 'user'])->name('playwright.user');
        });
    }
}
