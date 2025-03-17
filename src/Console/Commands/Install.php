<?php

namespace FranBarbaLopez\LaravelPlaywright\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (! $this->isPlaywrightInstalled()) {
            if ($this->confirm('Playwright is not installed. Would you like to install it?', false)) {
                $packageManager = $this->choice('Which package manager would you like to use?', ['npm', 'yarn', 'pnpm'], 0);

                $this->comment('Installing Playwright...');

                match ($packageManager) {
                    'npm' => Process::tty()->run('npm init playwright@latest'),
                    'yarn' => Process::tty()->run('yarn create playwright'),
                    'pnpm' => Process::tty()->run('pnpm create playwright'),
                };
            } else {
                $this->error('Playwright is required to install Laravel Playwright.');

                return;
            }
        }

        $paths = [
            'e2e' => (int) $this->files->exists(base_path('e2e')),
            'tests/e2e' => (int) $this->files->exists(base_path('tests/e2e')),
            'tests/Browser' => (int) $this->files->exists(base_path('tests/Browser')),
        ];

        $path = trim(strtolower((string) $this->ask('Whats the path of your Playwright tests?', array_flip($paths)[true] ?? 'e2e')));

        $this->comment('Publishing Laravel Playwright helper functions...');

        $this->files->copy(__DIR__.'/../../../dist/laravel-playwright.umd.js', base_path($path).'/laravel-playwright.js');

        $this->info('Laravel Playwright helper published successfully.');

        if ($this->files->exists(base_path('tsconfig.json'))) {
            $this->files->copy(__DIR__.'/../../../dist/types/laravel-playwright.d.ts', base_path($path).'/laravel-playwright.d.ts');

            $this->info('TypeScript definitions published successfully.');
        }

        $this->info('Laravel Playwright installed successfully.');
    }

    /**
     * Determine if Playwright is installed.
     */
    private function isPlaywrightInstalled(): bool
    {
        $packageJson = json_decode($this->files->get(base_path('package.json')), true);

        if (Arr::get($packageJson, 'devDependencies.@playwright/test')) {
            return true;
        }

        return (bool) Arr::get($packageJson, 'dependencies.@playwright/test');
    }
}
