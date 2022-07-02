<?php

namespace App\Console\Commands;

use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Services\RoleService;
use App\Services\UserService;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\EmployeeTableSeeder;
use Database\Seeders\ProductGroupTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\RoleTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\UserTableSeeder;
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
        if (! File::exists('.env')) {
            $this->error('File Not Found: .env');
            $this->error('Aborted');

            return false;
        }

        $option = $this->argument('option');
        $loop = true;

        while ($loop) {
            if (is_null($option)) {
                $this->info('Available Helper:');
                $this->info('[1] Update Composer And NPM           [7] Id <-> hId');
                $this->info('[2] Clear All Cache                   [8]');
                $this->info('[3] Change User Roles                 [9]');
                $this->info('[4] Data Seeding                      [10]');
                $this->info('[5] Wipe Database                     [11]');
                $this->info('[6] Create Administrator/Dev User     [12]');
                $this->info('[X] Exit');
            }

            $choose = is_null($option) ? $this->ask('Choose Helper', 'X') : $option;

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
                    $this->wipeDatabase();
                    break;
                case 6:
                    $this->createAdminDevUser();
                    break;
                case 7:
                    $this->encodeDecodeInputValue();
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

            if (! is_null($option)) {
                $loop = false;
            }
        }
        $this->info('Bye!');
    }

    private function dataSeeding()
    {
        if (App::environment('prod', 'production')) {
            $this->info('**************************************');
            $this->info('*     Application In Production!     *');
            $this->info('**************************************');

            $runInProd = $this->confirm('Do you really wish to run this command?', false);

            if (! $runInProd) {
                return;
            }
        }

        $unattended_mode = $this->confirm('Unattended Mode?', true);

        $seeders = [];
        if (! $unattended_mode) {
            $seeders = $this->choice(
                'Please select the seeders (comma separated for multiple choices):', [
                    'UserTableSeeder',
                    'RoleTableSeeder',
                    'CompanyTableSeeder',
                    'BranchTableSeeder',
                    'WarehouseTableSeeder',
                    'UnitTableSeeder',
                    'ProductTableSeeder',
                    'ProductGroupTableSeeder',
                    'BrandTableSeeder',
                    'SupplierTableSeeder',
                    'CustomerTableSeeder',
                    'EmployeeTableSeeder',
                ],
                null, 3, true
            );
        } else {
            $unattended_count = $this->ask('Override the seed count?', 5);
        }

        //region Seeders

        if (in_array('UserTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting UserTableSeeder');
            $truncate = $unattended_mode ? false : $this->confirm('Do you want to truncate the users table first?', false);
            $count = $unattended_mode ? $unattended_count : $this->ask('How many data:', 5);

            $this->info('Seeding...');

            $seeder = new UserTableSeeder();
            $seeder->callWith(UserTableSeeder::class, [$truncate, $count]);

            $this->info('UserTableSeeder Finish.');
        }

        if (in_array('RoleTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting RoleTableSeeder');
            $randomPermission = true;
            $count = $unattended_mode ? $unattended_count : $this->ask('How many data:', 5);

            $this->info('Seeding...');

            $seeder = new RoleTableSeeder();
            $seeder->callWith(RoleTableSeeder::class, [$randomPermission, $count]);

            $this->info('RoleTableSeeder Finish.');
        }

        if (in_array('CompanyTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting CompanyTableSeeder');
            $companiesPerUsers = $unattended_mode ? $unattended_count : $this->ask('How many companies for each users:', 3);
            $userId = $unattended_mode ? 0 : $this->ask('Only to this userId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new CompanyTableSeeder();
            $seeder->callWith(CompanyTableSeeder::class, [$companiesPerUsers, $userId]);

            $this->info('CompanyTableSeeder Finish.');
        }

        if (in_array('BranchTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting BranchTableSeeder');
            $branchPerCompanies = $unattended_mode ? $unattended_count : $this->ask('How many branches per company (0 to skip) :', 3);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new BranchTableSeeder();
            $seeder->callWith(BranchTableSeeder::class, [$branchPerCompanies, $onlyThisCompanyId]);

            $this->info('BranchTableSeeder Finish.');
        }

        if (in_array('WarehouseTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting WarehouseTableSeeder');
            $warehousePerCompanies = $unattended_mode ? $unattended_count : $this->ask('How many warehouses per company (0 to skip) :', 3);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new WarehouseTableSeeder();
            $seeder->callWith(WarehouseTableSeeder::class, [$warehousePerCompanies, $onlyThisCompanyId]);

            $this->info('WarehouseTableSeeder Finish.');
        }

        if (in_array('UnitTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting UnitTableSeeder');
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new UnitTableSeeder();
            $seeder->callWith(UnitTableSeeder::class, [$onlyThisCompanyId]);

            $this->info('UnitTableSeeder Finish.');
        }

        if (in_array('ProductGroupTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting ProductGroupTableSeeder');
            $productGroupPerCompany = $unattended_mode ? $unattended_count : $this->ask('How many product groups (0 to skip):', 3);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new ProductGroupTableSeeder();
            $seeder->callWith(ProductGroupTableSeeder::class, [$productGroupPerCompany, $onlyThisCompanyId]);

            $this->info('ProductGroupTableSeeder Finish.');
        }

        if (in_array('BrandTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting BrandTableSeeder');
            $brandPerCompany = $unattended_mode ? $unattended_count : $this->ask('How many brands (0 to skip):', 5);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new BrandTableSeeder();
            $seeder->callWith(BrandTableSeeder::class, [$brandPerCompany, $onlyThisCompanyId]);

            $this->info('BrandTableSeeder Finish.');
        }

        if (in_array('ProductTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting ProductTableSeeder');
            $productPerCompany = $unattended_mode ? $unattended_count : $this->ask('How many products for each companies:', 5);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new ProductTableSeeder();
            $seeder->callWith(ProductTableSeeder::class, [$productPerCompany, $onlyThisCompanyId]);

            $this->info('ProductTableSeeder Finish.');
        }

        if (in_array('SupplierTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting SupplierTableSeeder');
            $supplierPerCompany = $unattended_mode ? $unattended_count : $this->ask('How many supplier for each companies:', 5);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);

            $this->info('Seeding...');

            $seeder = new SupplierTableSeeder();
            $seeder->callWith(SupplierTableSeeder::class, [$supplierPerCompany, $onlyThisCompanyId]);

            $this->info('SupplierTableSeeder Finish.');
        }

        if (in_array('CustomerTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting CustomerTableSeeder');
            $count = $unattended_mode ? $unattended_count : $this->ask('How many customer for each companies:', 5);

            //$seeder = new CustomerTableSeeder();
            //$seeder->callWith(CustomerTableSeeder::class, [$count]);

            $this->info('CustomerTableSeeder Finish.');
        }

        if (in_array('EmployeeTableSeeder', $seeders) || $unattended_mode) {
            $this->info('Starting EmployeeTableSeeder');
            $count = $unattended_mode ? $unattended_count : $this->ask('How many employee for each branches in companies:', 5);
            $onlyThisCompanyId = $unattended_mode ? 0 : $this->ask('Only for this companyId (0 to all):', 0);
            $onlyThisBranchId = $unattended_mode ? 0 : $this->ask('Only for this branchId (0 to all):', 0);

            $seeder = new EmployeeTableSeeder();
            $seeder->callWith(EmployeeTableSeeder::class, [$count, $onlyThisCompanyId, $onlyThisBranchId]);

            $this->info('CustomerTableSeeder Finish.');
        }

        //endregion
    }

    private function wipeDatabase()
    {
        $this->info('THIS ACTIONS WILL WIPE CLEAN ALL TABLES. ALL DATA CANNOT BE RESTORED!');
        $run = $this->confirm('CONFIRM TO WIPE DATABASE?', false);

        if (App::environment('prod', 'production')) {
            $this->error('This helper cannot run on production enviroment');
            $run = false;
        }

        if ($run) {
            Artisan::call('db:wipe');
            $this->info(Artisan::output());
            sleep(3);
            $this->info('Done.');
        }
    }

    private function encodeDecodeInputValue()
    {
        if (! is_null($this->argument('option'))) {
            $args = $this->argument('args');

            $input = $args[0];
        } else {
            $input = $this->ask('Enter value:', '');
        }

        try {
            if (is_numeric($input)) {
                $this->info('Assuming input value as Id.');
                $this->info('hId: '.Hashids::encode($input));
            } else {
                $this->info('Assuming input value as hId.');
                $this->info('Id: '.Hashids::decode($input)[0]);
            }
        } catch (Exception $e) {
            $this->info('Input is invalid.');
        }
    }

    private function createAdminDevUser()
    {
        if (! is_null($this->argument('option'))) {
            $args = $this->argument('args');

            $userName = $args[0];
            $userEmail = $args[1];
            $userPassword = $args[2];
            $is_dev = boolval($args[3]);

            $this->info('Creating Account...');
            $this->info('Name: '.$userName);
            $this->info('Email: '.$userEmail);
            $this->info('Password: '.'***********');
            $this->info('Account Type: '.($is_dev ? 'Developers' : 'Administrator'));
        } else {
            $this->info('Creating Admin/Dev Account ...');
            $is_dev = $this->confirm('Are you a developer?', false);

            if (! $is_dev) {
                $this->info('Setting you account as administrator since you\'re not dev...');
            }

            $userName = 'GitzJoey';
            $userEmail = 'gitzjoey@yahoo.com';
            $userPassword = 'thepassword';

            $valid = false;

            while (! $valid) {
                $userName = $this->ask('Name:', $userName);
                $userEmail = $this->ask('Email:', $userEmail);
                $userPassword = $this->secret('Password:', $userPassword);

                $validator = Validator::make([
                    'name' => $userName,
                    'email' => $userEmail,
                    'password' => $userPassword,
                ], [
                    'name' => 'required|min:3|max:50',
                    'email' => 'required|max:255|email|unique:users,email',
                    'password' => 'required|min:7',
                ]);

                if (! $validator->fails()) {
                    $valid = true;
                } else {
                    foreach ($validator->errors()->all() as $errorMessage) {
                        $this->error($errorMessage);
                    }
                }
            }

            $confirmed = $this->confirm("Everything's OK? Do you wish to continue?");

            if (! $confirmed) {
                $this->error('Aborted');

                return false;
            }
        }

        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $rolesId = [];
        if ($is_dev) {
            array_push($rolesId, $roleService->readBy('NAME', UserRoles::DEVELOPER->value)->id);
        } else {
            array_push($rolesId, $roleService->readBy('NAME', UserRoles::ADMINISTRATOR->value)->id);
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

        $userService->create(
            $user,
            $rolesId,
            $profile
        );

        $this->info('User Creation Success.');
    }

    private function changeUserRoles()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $valid = false;

        $email = '';

        while (! $valid) {
            $email = $this->ask('Email:', $email);

            $usr = $userService->readby('EMAIL', $email);

            if ($usr) {
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                $mode = $this->choice('Do you want to attach or remove?', ['Attach', 'Remove']);

                $this->info('Available Roles: '.$roleService->read()->pluck('display_name'));
                $roleDisplayName = $this->ask('Please Select From Available Roles: ');

                $role = $roleService->readBy('DISPLAY_NAME', $roleDisplayName);

                if (! $role) {
                    $this->error('Invalid Role');

                    return false;
                }

                $confirmed = $this->confirm("Proceed to $mode Role $role->display_name to $usr->name?", true);

                if (! $confirmed) {
                    $this->error('Aborted');

                    return false;
                }

                if ($mode == 'Attach') {
                    $usr->attachRole($role);
                } elseif ($mode == 'Remove') {
                    $usr->detachRole($role);
                } else {
                }

                $this->info('Done');
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                sleep(3);

                $confirmedExit = $this->confirm('Do you want to attach/remove another role?', false);

                if (! $confirmedExit) {
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
