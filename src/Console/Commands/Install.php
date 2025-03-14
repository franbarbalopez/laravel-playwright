<?php

namespace FranBarbaLopez\LaravelPlaywright\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-playwright:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Laravel Playwright resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (! $this->isPlaywrightInstalled()) {
            $this->proposePlaywrightInstallation();
        }

        $this->comment('Publishing Laravel Playwright Assets...');
        $this->callSilently('vendor:publish', ['--tag' => 'laravel-playwright-assets']);

        $this->info('Laravel Playwright scaffolding installed successfully.');
    }

    /**
     * Determine if Playwright is installed.
     * 
     * @return bool
     */
    private function isPlaywrightInstalled(): bool
    {
        $packageJson = json_decode(base_path('package.json'), true);

        return Arr::get($packageJson, 'devDependencies.@playwright/test') || Arr::get($packageJson, 'dependencies.@playwright/test');
    }

    private function proposePlaywrightInstallation(): void
    {
        if ($this->confirm('Playwright is not installed. Would you like to install it?', false)) {
            $packageManager = $this->choice('Which package manager would you like to use?', ['npm', 'yarn', 'pnpm'], 0);
        
            $this->comment('Installing Playwright...');

            match ($packageManager) {
                'npm' => $this->callSilently('npm init playwright@latest'),
                'yarn' => $this->callSilently('yarn create playwright'),
                'pnpm' => $this->callSilently('pnpm create playwright'),
            };
        } else {
            $this->warn('Playwright is required to install Laravel Playwright.');

            return;
        }
    }
}