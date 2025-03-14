<?php

namespace FranBarbaLopez\LaravelPlaywright\Console\Commands;

use Illuminate\Console\Command;

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
            return;
        }

        $this->comment('Publishing Laravel Playwright Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-playwright-assets']);

        $this->info('Laravel Playwright scaffolding installed successfully.');
    }

    private function isPlaywrightInstalled()
    {
        dd(base_path());
        return file_exists(base_path('package.json'));
    }
}