<?php

namespace App\Console\Commands;

use App\Actions\Role\RoleActions;
use App\Actions\System\SystemActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
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
        $this->info('Review this installation process in \App\Console\Commands\Install.php');

        sleep(3);

        switch (strtolower($this->argument('args'))) {
            case 'dev':
            case 'admin':
                $this->createAdminOrDevAccount(strtolower($this->argument('args')));
                break;
            default:
                $this->defaultInstallation();
                break;
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function defaultInstallation(): void
    {
        $passPreInstallCheck = $this->passPreInstallCheck();
        if (! $passPreInstallCheck) {
            return;
        }

        $systemCheckIsOK = $this->systemCheckingIsOK();
        if (! $systemCheckIsOK) {
            return;
        }

        $this->generateAppKey();
        $this->seedingData();
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

    private function seedingData(): void
    {
        if (App::environment('prod', 'production')) {
            $this->info('[PROD] Seeding ...');
            Artisan::call('db:seed', [
                '--force' => true,
            ]);
        } else {
            $this->info('Seeding ...');
            Artisan::call('db:seed');
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

    private function createAdminOrDevAccount(string $accountType): void
    {
        $this->info('Creating '.($accountType == 'dev' ? 'Dev' : 'Admin').' Account ...');

        $userName = 'GitzJoey';
        $userEmail = 'gitzjoey@yahoo.com';
        $userPassword = 'thepassword';

        $invalid = true;

        $userActions = new UserActions();
        $roleActions = new RoleActions();

        do {
            $userName = $this->ask('Name', $userName);
            $userEmail = $this->ask('Email', $userEmail);
            $userPassword = $this->secret('Password', $userPassword);

            $is_dev = $accountType == 'dev' ? true : false;

            $rolesId = [];
            if ($is_dev) {
                array_push($rolesId, $roleActions->readBy('NAME', UserRoles::DEVELOPER->value)->id);
            } else {
                array_push($rolesId, $roleActions->readBy('NAME', UserRoles::ADMINISTRATOR->value)->id);
            }

            $profile = [
                'first_name' => $userName,
                'country' => 'Singapore',
                'status' => RecordStatus::ACTIVE,
            ];

            $user = [
                'name' => $userName,
                'email' => $userEmail,
                'password' => $userPassword,
            ];

            $confirmed = $this->confirm("Everything's OK? Do you wish to continue?");

            try {
                if (! $confirmed) {
                    $this->error('Aborted');

                    $invalid = false;
                } else {
                    $userActions->create(
                        $user,
                        $rolesId,
                        $profile
                    );

                    $this->info('Creating Account...');
                    $this->info('Name: '.$userName);
                    $this->info('Email: '.$userEmail);
                    $this->info('Password: '.'***********');
                    $this->info('Account Type: '.($is_dev ? 'Developers' : 'Administrator'));

                    $invalid = false;
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
                $this->info('');
                $this->error('Retrying...');
            }
        } while ($invalid);
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
