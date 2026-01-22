<?php

namespace App\Console\Commands;

use App\Actions\Role\RoleActions;
use App\Actions\System\SystemActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatusEnum;
use App\Enums\UserRolesEnum;
use Database\Seeders\BranchSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CashAccountSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CustomerGroupSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\InvestorSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\StockAdjustmentCategorySeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\WarehouseSeeder;
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

        (new CompanySeeder())->run();
        (new BranchSeeder())->run();
        (new WarehouseSeeder())->run();

        (new CashAccountSeeder())->run();
        (new InvestorSeeder())->run();

        (new ProductCategorySeeder())->run();
        (new BrandSeeder())->run();
        (new UnitSeeder())->run();

        (new CustomerGroupSeeder())->run();
        (new CustomerSeeder())->run();

        (new StockAdjustmentCategorySeeder())->run();

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
        $this->createDevAccount();
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

    private function createDevAccount(): void
    {
        $this->info('Creating Developer Account...');

        $userActions = new UserActions();
        $roleActions = new RoleActions();

        // Get Developer role
        $roleName = ucfirst(UserRolesEnum::DEVELOPER->value);

        try {
            $role = $roleActions->readBy('NAME', $roleName);

            if (! $role) {
                $this->error('Role "'.$roleName.'" not found. Skipping account creation.');
                $this->warn('Make sure you have run the seeders to create roles.');

                return;
            }

            // Default user data
            $userName = 'Developer';
            $userEmail = 'dev@app.com';
            $userPassword = 'password';

            // Check if user already exists
            $existingUser = $userActions->readBy('EMAIL', $userEmail);
            if ($existingUser) {
                $this->warn('User with email "'.$userEmail.'" already exists. Skipping account creation.');

                return;
            }

            $user = [
                'name' => $userName,
                'email' => $userEmail,
                'password' => $userPassword,
            ];

            $profile = [
                'first_name' => $userName,
                'last_name' => '',
                'tax_id' => 0,
                'ic_num' => 0,
                'country' => 'Singapore',
                'status' => RecordStatusEnum::ACTIVE,
            ];

            $userActions->create(
                $user,
                [$role->id],
                $profile
            );

            $this->info('✓ Developer account created successfully!');
            $this->info('  Name: '.$userName);
            $this->info('  Email: '.$userEmail);
            $this->info('  Password: '.$userPassword);
            $this->info('  Role: '.$role->display_name);
            $this->warn('  ⚠ Please change the default password after first login!');
        } catch (Exception $e) {
            $this->error('Failed to create developer account: '.$e->getMessage());
            $this->warn('You can create an account manually using: php artisan app:user create');
        }
    }
}
