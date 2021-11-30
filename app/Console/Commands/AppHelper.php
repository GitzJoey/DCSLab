<?php

namespace App\Console\Commands;

use App\Services\RoleService;
use App\Services\UserService;

use Database\Seeders\UserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\ProductGroupTableSeeder;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AppHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper for this applications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!File::exists('.env')) {
            $this->error('File Not Found: .env');
            $this->error('Aborted');
            return false;
        }

        $loop = true;

        while($loop)
        {
            $this->info('Available Helper:');
            $this->info('[1] Update Composer And NPM');
            $this->info('[2] Clear All Cache');
            $this->info('[3] Change User Roles');
            $this->info('[4] Data Seeding');
            $this->info('[5] Refresh Database');
            $this->info('[X] Exit');

            $choose = $this->ask('Choose Helper','X');

            switch (strtoupper($choose)) {
                case 1:
                    $this->updateComposerAndNPM();
                    $this->info('Done!');
                    break;
                case 2:
                    $this->clearCache();
                    $this->info('Done!');
                    break;
                case 3:
                    $this->changeUserRoles();
                    $this->info('Done!');
                    break;
                case 4:
                    $this->dataSeeding();
                    $this->info('Done.');
                    break;
                case 5:
                    $this->refreshDatabase();
                    break;
                case 'X':
                default:
                    $loop = false;
                    break;
            }
            sleep(3);
        }
        $this->info('Bye!');
    }

    private function dataSeeding()
    {
        $user = $this->confirm('Do you want to seed users?', true);
        $roles = $this->confirm('Do you want to seed roles?', true);
        $companies = $this->confirm('Do you want to seed companies for each users?', true);
        $supplier = $this->confirm('Do you want to seed dummy suppliers for each companies?', true);
        $product = $this->confirm('Do you want to seed dummy products for each companies?', true);

        if ($user)
        {
            $this->info('Starting UserSeeder');
            $truncate = $this->confirm('Do you want to truncate the users table first?', false);
            $count = $this->ask('How many data:', 100);

            $this->info('Seeding...');

            $seeder = new UserSeeder();
            $seeder->callWith(UserSeeder::class, [$truncate, $count]);

            $this->info('UserSeeder Finish.');
        }

        sleep(3);

        if ($roles)
        {
            $this->info('Starting RoleSeeder');
            $count = $this->ask('How many data:', 10);

            $this->info('Seeding...');

            $seeder = new RoleSeeder();
            $seeder->callWith(RoleSeeder::class, [true, $count]);

            $this->info('RoleSeeder Finish.');
        }

        sleep(3);

        if ($companies)
        {
            $this->info('Starting CompanyTableSeeder');
            $count = $this->ask('How many companies for each users:', 5);

            $seeder = new CompanyTableSeeder();
            $seeder->callWith(CompanyTableSeeder::class, [$count]);

            $this->info('CompanyTableSeeder Finish.');
        }

        sleep(3);

        if ($supplier)
        {
            $this->info('Starting SupplierTableSeeder');
            $count = $this->ask('How many supplier for each companies:', 5);

            $seeder = new SupplierTableSeeder();
            $seeder->callWith(SupplierTableSeeder::class, [$count]);

            $this->info('SupplierTableSeeder Finish.');
        }

        sleep(3);

        if ($product)
        {
            $this->info('Starting ProductGroupSeeder, BrandSeeder before ProductTableSeeder');

            $count_pg = $this->ask('How many product groups:', 5);
            $count_pb = $this->ask('How many brands:', 10);

            $this->info('Starting ProductGroupSeeder and BrandSeeder');

            $seeder_pg = new ProductGroupTableSeeder();
            $seeder_pg->callWith(ProductGroupTableSeeder::class, [$count_pg]);

            $seeder_pb = new BrandTableSeeder();
            $seeder_pb->callWith(BrandTableSeeder::class, [$count_pb]);

            $this->info('Starting ProductTableSeeder');
            $count = $this->ask('How many products for each companies:', 15);

            $seeder = new ProductTableSeeder();
            $seeder->callWith(ProductTableSeeder::class, [$count]);

            $this->info('ProductTableSeeder Finish.');
        }
    }

    private function refreshDatabase()
    {
        $this->info('THIS ACTIONS WILL REMOVE ALL DATA AND LEAVING ONLY DEFAULT DATA');
        $run = $this->confirm('CONFIRM TO REFRESH DATABASE?', false);

        Schema::disableForeignKeyConstraints();

        DB::table('activity_log')->truncate();
        DB::table('companies')->truncate();
        DB::table('company_user')->truncate();
        DB::table('brands')->truncate();
        DB::table('product_groups')->truncate();
        DB::table('product_unit')->truncate();
        DB::table('products')->truncate();
        DB::table('suppliers')->truncate();

        Schema::enableForeignKeyConstraints();
    }

    private function changeUserRoles()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $valid = false;

        $email = '';

        while (!$valid) {
            $email = $this->ask('Email:', $email);

            $usr = $userService->getUserByEmail($email);

            if ($usr) {
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                $mode = $this->choice('Do you want to attach or remove?', ['Attach', 'Remove']);

                $this->info('Available Roles: '.$roleService->read()->pluck('display_name'));
                $roleDisplayName = $this->ask('Please Select From Available Roles: ');

                $role = $roleService->getRoleByDisplayName($roleDisplayName, false);

                if (!$role) {
                    $this->error('Invalid Role');
                    return false;
                }

                $confirmed = $this->confirm("Proceed to $mode Role $role->display_name to $usr->name?", true);

                if (!$confirmed) {
                    $this->error('Aborted');
                    return false;
                }

                if ($mode == 'Attach') {
                    $usr->attachRole($role);
                } else if ($mode == 'Remove') {
                    $usr->detachRole($role);
                } else {

                }

                $this->info('Done');
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                sleep(3);

                $confirmedExit = $this->confirm("Do you want to attach/remove another role?", false);

                if (!$confirmedExit) {
                    $this->error('Exiting');
                    return false;
                }
            }
        }
    }

    private function clearCache()
    {
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('clear-compiled');
    }

    private function updateComposerAndNPM()
    {
        $this->info('Starting Composer Update');
        exec('composer update');

        $this->info('Starting NPM Update');
        exec('npm update');

        $this->info('Starting Mix');
        if (App::environment('prod', 'production')) {
            $this->info('Executing for production enviroment');
            exec('npm run prod');
        } else {
            exec('npm run dev');
        }
    }
}
