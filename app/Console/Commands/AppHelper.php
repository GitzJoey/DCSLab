<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Services\RoleService;
use App\Services\UserService;

use Database\Seeders\UnitTableSeeder;
use Database\Seeders\UserTableSeeder;
use Database\Seeders\RoleTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\ProductGroupTableSeeder;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\WarehouseTableSeeder;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class AppHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:helper {option? : Select the available options} {args?* : Optional arguments}';

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

        $option = $this->argument('option');
        $loop = true;

        while($loop)
        {
            if (is_null($option)) {
                $this->info('Available Helper:');
                $this->info('[1] Update Composer And NPM           [7] Convert hId to Id');
                $this->info('[2] Clear All Cache                   [8]');
                $this->info('[3] Change User Roles                 [9]');
                $this->info('[4] Data Seeding                      [10]');
                $this->info('[5] Refresh Database                  [11]');
                $this->info('[6] Create Acministrator/Dev User     [12]');
                $this->info('[X] Exit');
            }

            $choose = is_null($option) ? $this->ask('Choose Helper','X') : $option;

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
                case 6:
                    $this->createAdminDevUser();
                    break;
                case 7:
                    $this->convertHashIdToId();
                    break;
                case 'X':
                    $loop = false;
                    break;
                default:
                    $this->info('Invalid Options.');
                    $loop = false;
                    break;
            }
            sleep(3);
            
            if (!is_null($option)) $loop = false;
        }
        $this->info('Bye!');
    }

    private function convertHashIdToId()
    {
        if (!is_null($this->argument('option'))) {
            $args = $this->argument('args');

            $hId = $args[0];
        } else {
            $hId = $this->ask('Enter hId:', '');
        }

        try {
            $this->info('Id: ' . Hashids::decode($hId)[0]);
        } catch(Exception $e) {
            $this->info('Id: '.'Invalid');
        }
    }

    private function createAdminDevUser()
    {
        if (!is_null($this->argument('option'))) {
            $args = $this->argument('args');

            $userName = $args[0];
            $userEmail = $args[1];
            $userPassword = $args[2];
            $is_dev = boolval($args[3]);

            $this->info('Creating Account...');
            $this->info('Name: '.$userName);
            $this->info('Email: '.$userEmail);
            $this->info('Password: '.'***********');
            $this->info('Account Type: '. ($is_dev ? 'Developers':'Administrator'));
        } else {
            $this->info('Creating Admin/Dev Account ...');
            $is_dev = $this->confirm("Are you a developer?", false);
    
            if(!$is_dev)
                $this->info('Setting you account as administrator since you\'re not dev...');
    
            $userName = 'GitzJoey';
            $userEmail = 'gitzjoey@yahoo.com';
            $userPassword = 'thepassword';
    
            $valid = false;
    
            while (!$valid) {
                $userName = $this->ask('Name:', $userName);
                $userEmail = $this->ask('Email:', $userEmail);
                $userPassword = $this->secret('Password:', $userPassword);
    
                $validator = Validator::make([
                    'name' => $userName,
                    'email' => $userEmail,
                    'password' => $userPassword
                ], [
                    'name' => 'required|min:3|max:50',
                    'email' => 'required|max:255|email|unique:users,email',
                    'password' => 'required|min:7'
                ]);
    
                if (!$validator->fails()) {
                    $valid = true;
                } else {
                    foreach ($validator->errors()->all() as $errorMessage) {
                        $this->error($errorMessage);
                    }
                }
            }
    
            $confirmed = $this->confirm("Everything's OK? Do you wish to continue?");
    
            if (!$confirmed) {
                $this->error('Aborted');
                return false;
            }    
        }

        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $rolesId = [];
        if ($is_dev) {
            array_push($rolesId, $roleService->readBy('NAME', Config::get('const.DEFAULT.ROLE.DEV'))->id);
        } else {
            array_push($rolesId, $roleService->readBy('NAME', Config::get('const.DEFAULT.ROLE.ADMIN'))->id);
        }

        $profile = [
            'first_name' => $userName,
            'status' => 1
        ];

        $userService->create(
            $userName,
            $userEmail,
            $userPassword,
            $rolesId,
            $profile
        );

        $this->info('User Creation Success.');
    }

    private function dataSeeding()
    {
        if (App::environment('prod', 'production')) {
            $this->info('**************************************');
            $this->info('*     Application In Production!     *');
            $this->info('**************************************');
        
            $runInProd = $this->confirm('Do you really wish to run this command?', false);

            if (!$runInProd) {
                return;
            }
        }

        $unattended_mode = $this->confirm('Unattended Mode?', true);

        $user = $unattended_mode ? true : $this->confirm('Do you want to seed users?', true);
        $roles = $unattended_mode ? true : $this->confirm('Do you want to seed roles?', true);
        $companies = $unattended_mode ? true : $this->confirm('Do you want to seed companies for each users?', true);
        $supplier = $unattended_mode ? true : $this->confirm('Do you want to seed dummy suppliers for each companies?', true);
        $product = $unattended_mode ? true : $this->confirm('Do you want to seed dummy products for each companies?', true);
        $customer = $unattended_mode ? true : $this->confirm('Do you want to seed dummy customers for each companies?', true);

        if (Permission::count() == 0) {
            $this->info('Roles and Permissions table is empty. seeding...');
            Artisan::call('db:seed');
        }

        if ($user)
        {
            $this->info('Starting UserTableSeeder');
            $truncate = $unattended_mode ? false : $this->confirm('Do you want to truncate the users table first?', false);
            $count = $unattended_mode ? 5 : $this->ask('How many data:', 5);

            $this->info('Seeding...');

            $seeder = new UserTableSeeder();
            $seeder->callWith(UserTableSeeder::class, [$truncate, $count]);

            $this->info('UserTableSeeder Finish.');
        }

        sleep(3);

        if ($roles)
        {
            $this->info('Starting RoleTableSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many data:', 5);

            $this->info('Seeding...');

            $seeder = new RoleTableSeeder();
            $seeder->callWith(RoleTableSeeder::class, [true, $count]);

            $this->info('RoleTableSeeder Finish.');
        }

        sleep(3);

        if ($companies)
        {
            $this->info('Starting CompanyTableSeeder');
            $count = $unattended_mode ? 3 : $this->ask('How many companies for each users:', 3);

            $seeder = new CompanyTableSeeder();
            $seeder->callWith(CompanyTableSeeder::class, [$count]);

            $this->info('CompanyTableSeeder Finish.');

            $this->info('Starting BranchTableSeeder');
            $count_br = $unattended_mode ? 3 : $this->ask('How many branches per company (0 to skip) :', 3);

            if ($count_br != 0) {
                $seeder_br = new BranchTableSeeder();
                $seeder_br->callWith(BranchTableSeeder::class, [$count_br]);
    
                $this->info('BranchTableSeeder Finish.');    
            }

            $this->info('Starting WarehouseTableSeeder');
            $count_wh = $unattended_mode ? 3 : $this->ask('How many warehouses per company (0 to skip) :', 3);

            if ($count_wh != 0) {
                $seeder_wh = new WarehouseTableSeeder();
                $seeder_wh->callWith(WarehouseTableSeeder::class, [$count_wh]);
    
                $this->info('WarehouseTableSeeder Finish.');
            }
        }

        sleep(3);

        if ($product)
        {
            $this->info('Starting UnitTableSeeder');
            $seeder_unit = new UnitTableSeeder();
            $seeder_unit->callWith(UnitTableSeeder::class);
            $this->info('UnitTableSeeder Finish.');

            $this->info('Starting ProductGroupTableSeeder');
            $count_pg = $unattended_mode ? 3 : $this->ask('How many product groups (0 to skip):', 3);

            if ($count_pg != 0) {
                $seeder_pg = new ProductGroupTableSeeder();
                $seeder_pg->callWith(ProductGroupTableSeeder::class, [$count_pg]);

                $this->info('ProductGroupTableSeeder Finish.');
            }

            $this->info('Starting BrandTableSeeder');
            $count_pb = $unattended_mode ? 5 : $this->ask('How many brands (0 to skip):', 5);

            if ($count_pb != 0) {

                $seeder_pb = new BrandTableSeeder();
                $seeder_pb->callWith(BrandTableSeeder::class, [$count_pb]);

                $this->info('BrandTableSeeder Finish.');
            }

            $this->info('Starting ProductTableSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many products for each companies:', 5);

            $seeder = new ProductTableSeeder();
            $seeder->callWith(ProductTableSeeder::class, [$count]);

            $this->info('ProductTableSeeder Finish.');
        }

        sleep(3);

        if ($supplier)
        {
            $this->info('Starting SupplierTableSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many supplier for each companies:', 5);

            $seeder = new SupplierTableSeeder();
            $seeder->callWith(SupplierTableSeeder::class, [$count]);

            $this->info('SupplierTableSeeder Finish.');
        }

        sleep(3);

        if ($customer)
        {
            $this->info('Starting CustomerTableSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many customer for each companies:', 5);

            //$seeder = new CustomerTableSeeder();
            //$seeder->callWith(CustomerTableSeeder::class, [$count]);

            $this->info('CustomerTableSeeder Finish.');
        }
    }

    private function refreshDatabase()
    {
        $this->info('THIS ACTIONS WILL REMOVE ALL DATA AND LEAVING ONLY DEFAULT DATA');
        $run = $this->confirm('CONFIRM TO REFRESH DATABASE?', false);

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->info('Done.');
        sleep(3);
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

            $usr = $userService->readby('EMAIL', $email);

            if ($usr) {
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                $mode = $this->choice('Do you want to attach or remove?', ['Attach', 'Remove']);

                $this->info('Available Roles: '.$roleService->read()->pluck('display_name'));
                $roleDisplayName = $this->ask('Please Select From Available Roles: ');

                $role = $roleService->readBy('DISPLAY_NAME', $roleDisplayName);

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
