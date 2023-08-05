<?php

namespace App\Console\Commands;

use App\Actions\System\SystemActions;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install {args?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'App Installation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting App Installation...');

        sleep(3);

        switch (strtolower($this->argument('args'))) {
            default:
                $this->defaultInstallation();
                break;
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function defaultInstallation(): void
    {
        $this->info('Starting Default Installation');

        $passPreInstallCheck = $this->passPreInstallCheck();
        if (! $passPreInstallCheck) {
            return;
        }

        $systemCheckIsOK = $this->systemCheckingIsOK();
        if (! $systemCheckIsOK) {
            return;
        }

        $this->generateAppKey();
        $this->migrateAndSeed();
        $this->storageLinking();
        $this->createAdminOrDevAccount('admin');
    }

    private function systemCheckingIsOK(): bool
    {
        $result = true;

        $systemActions = new SystemActions();

        if (! $systemActions->checkDBConnection()) {
            $this->error('Database Connection Fail. Message: '.$systemActions->getDBConnectionError());
            $this->error('Aborted');

            $result = false;
        }

        if ($systemActions->isExistTable('users')) {
            $this->error('Table User Found, Please DROP first');
            $this->error('Aborted');

            $result = false;
        }

        return $result;
    }

    private function generateAppKey(): void
    {
        $this->info('Generating App Key...');
        if (App::environment('prod', 'production')) {
            Artisan::call('key:generate', [
                '--force' => true,
            ]);
        } else {
            Artisan::call('key:generate');
        }

        $this->info(Artisan::output());
    }

    private function migrateAndSeed(): void
    {
        if (App::environment('prod', 'production')) {
            $this->info('[PROD] Migrating & Seeding ...');
            Artisan::call('migrate', [
                '--seed' => true,
            ]);
        } else {
            $this->info('Migrating & Seeding ...');
            Artisan::call('migrate', ['--seed' => true]);
        }

        $this->info(Artisan::output());
    }

    private function storageLinking(): void
    {
        $this->info('Storage Linking ...');
        if (is_link(public_path().'/storage')) {
            $this->info('Found Storage Link, Skipping ...');
        } else {
            Artisan::call('storage:link');
        }

        $this->info(Artisan::output());
    }

    private function passPreInstallCheck()
    {
        if (! File::exists('.env')) {
            $this->error('File Not Found: .env');
            $this->error('Aborted');

            return false;
        }

        if (env('DB_PASSWORD', '') == '') {
            $this->error('Database not configured properly');
            $this->error('Aborted');

            return false;
        }

        if (env('DCSLAB_DATACACHE', true)) {
            if (! $this->checkRedisConnection()) {
                $this->error('Data cache is enabled but Redis not configured properly');
                $this->error('Aborted');

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

        return true;
    }

    private function checkRedisConnection()
    {
        $redis = Redis::connection();
        try {
            $redis->ping();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
