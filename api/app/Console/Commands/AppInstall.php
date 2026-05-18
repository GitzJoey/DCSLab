<?php

namespace App\Console\Commands;

use App\Actions\System\SystemActions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

#[Signature('app:install {args=default}')]
#[Description('DCSLab App Installation')]
class AppInstall extends Command
{
    public function handle()
    {
        $this->components->info('Starting DCSLab App Installation...');

        sleep(3);

        $preInstallationCheck = $this->preInstallationCheck();

        if (! $preInstallationCheck) {
            $this->components->error('Aborted');

            return Command::FAILURE;
        }

        switch (strtolower($this->argument('args'))) {
            default:
                $this->defaultInstallation();
                break;
        }

        $this->components->info('Done!');

        return Command::SUCCESS;
    }

    private function defaultInstallation()
    {
        $this->components->info('Running default installation...');

        $this->components->task('Generating Application Key', fn () => $this->generateAppKey());
        $this->components->task('Migrating & Seeding', fn () => $this->migrateAndSeed());
        $this->components->task('Linking Storage', fn () => $this->storageLinking());
    }

    private function generateAppKey(): bool
    {
        if (App::isProduction()) {
            Artisan::call('key:generate', [
                '--force' => true,
            ]);
        } else {
            Artisan::call('key:generate');
        }

        return true;
    }

    private function migrateAndSeed(): bool
    {
        if (App::isProduction()) {
            Artisan::call('migrate', [
                '--seed' => true,
            ]);
        } else {
            Artisan::call('migrate', ['--seed' => true]);
        }

        return true;
    }

    private function storageLinking(): bool
    {
        if (! is_link(public_path().'/storage')) {
            Artisan::call('storage:link');
        }

        return true;
    }

    private function preInstallationCheck()
    {
        if (! File::exists('.env')) {
            $this->error('File Not Found: .env');

            return false;
        }

        if (env('DB_PASSWORD', '') == '') {
            $this->error('Database not configured properly');

            return false;
        }

        $systemActions = new SystemActions;

        if (env('DCSLAB_DATACACHE', true)) {
            if (! $systemActions->checkRedisConnection()) {
                $this->error('Data cache is enabled but Redis not configured properly');

                return false;
            }
        }

        if ((env('BROADCAST_DRIVER') == 'pusher' && empty(env('PUSHER_APP_KEY')))) {
            $this->error('Pusher not configured properly');

            return false;
        }

        if ((env('BROADCAST_DRIVER') == 'soketi' && empty(env('SOKETI_APP_KEY')))) {
            $this->error('Soketi not configured properly');

            return false;
        }

        if (! $systemActions->checkDBConnection()) {
            $this->error('Database Connection Fail. Message: '.$systemActions->getDBConnectionError());

            return false;
        }

        if ($systemActions->isExistTable('users')) {
            $this->error('Table User Found, Please DROP first');

            return false;
        }

        return true;
    }
}
