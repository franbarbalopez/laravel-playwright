<?php

namespace FranBarbaLopez\LaravelPlaywright\Tests;

use FranBarbaLopez\LaravelPlaywright\Http\Controllers\PlaywrightController;
use Illuminate\Database\Eloquent\Factories\Factory;
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
