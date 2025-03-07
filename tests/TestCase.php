<?php

namespace FranBarbaLopez\LaravelPlaywright\Tests;

use FranBarbaLopez\LaravelPlaywright\Http\Controllers\PlaywrightController;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Routing\Router;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[WithEnv('DB_CONNECTION', 'testing')]
abstract class TestCase extends BaseTestCase
{
    use WithWorkbench, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Workbench\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });
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
        });
    }
}
