<?php

namespace App\Console\Commands;

use Database\Seeders\BranchTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\EmployeeTableSeeder;
use Database\Seeders\ProductGroupTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Database\Seeders\RoleTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\UserTableSeeder;
use Database\Seeders\WarehouseTableSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class AppSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed {args?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data Seeding';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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

        $argsArr = [];

        if (! empty($this->argument('args'))) {
            if (str_contains($this->argument('args'), ',')) {
                $argsArr = explode(',', $this->argument('args'));
            } else {
                $argsArr = [
                    $this->argument('args'),
                ];
            }
            $this->runWithArgs($argsArr);
        } else {
            $this->runDefault();
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function runWithArgs(array $argsArr)
    {
        foreach ($argsArr as $args) {
            switch (strtolower($args)) {
                case 'user':
                case 'usertableseeder':
                    $this->runUserTableSeederInteractive();
                    break;
                case 'role':
                case 'roletableseeder':
                    $this->runRoleTableSeederInteractive();
                    break;
                case 'company':
                case 'companytableseeder':
                    $this->runCompanyTableSeederInteractive();
                    break;
                case 'branch':
                case 'branchtableseeder':
                    $this->runBranchTableSeederInteractive();
                    break;
                case 'employee':
                case 'employeetableseeder':
                    $this->runEmployeeTableSeederInteractive();
                    break;
                case 'warehouse':
                case 'warehousetableseeder':
                    $this->runWarehouseTableSeederInteractive();
                    break;
                case 'productgroup':
                case 'productgrouptableseeder':
                    $this->runProductGroupTableSeederInteractive();
                    break;
                case 'brand':
                case 'brandtableseeder':
                    $this->runBrandTableSeederInteractive();
                case 'unit':
                case 'unittableseeder':
                    $this->runUnitTableSeederInteractive();
                    break;
                case 'product':
                case 'producttableseeder':
                    $this->runProductTableSeederInteractive();
                    break;
                case 'supplier':
                case 'suppliertableseeder':
                    $this->runSupplierTableSeederInteractive();
                    break;
                case 'customergroup':
                case 'customergrouptableseeder':
                    $this->runCustomerGroupTableSeederInteractive();
                    break;
                default:
                    $this->info('Cannot find seeder for '.$args);
                    break;
            }
        }
    }

    private function runDefault()
    {
        $total = 12;
        $this->info('');
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $this->runUserTableSeeder(false, 5);
        $progressBar->advance();
        $this->runRoleTableSeeder(true, 5);
        $progressBar->advance();
        $this->runCompanyTableSeeder(3, 0);
        $progressBar->advance();
        $this->runBranchTableSeeder(3, 0);
        $progressBar->advance();
        $this->runEmployeeTableSeeder(3, 0, 0);
        $progressBar->advance();
        $this->runWarehouseTableSeeder(3, 0);
        $progressBar->advance();
        $this->runProductGroupTableSeeder(3, 0, 0);
        $progressBar->advance();
        $this->runBrandTableSeeder(10, 0);
        $progressBar->advance();
        $this->runUnitTableSeeder(3, 0, 0);
        $progressBar->advance();
        $this->runProductTableSeeder(5, 0, 0); 
        $progressBar->advance();
        $this->runSupplierTableSeeder(3, 0); 
        $progressBar->advance();
        $this->runCustomerGroupTableSeeder(3, 0); 
        $progressBar->advance();

        $progressBar->finish();
        $this->info('');
        $this->info('');
    }

    private function runUserTableSeederInteractive()
    {
        $this->info('Starting UserTableSeeder');
        $truncate = $this->confirm('Do you want to truncate the users table first?', false);
        $count = $this->ask('How many data:', 5);

        $this->info('Seeding...');

        $this->runUserTableSeeder($truncate, $count);

        $this->info('UserTableSeeder Finish.');
    }

    private function runUserTableSeeder($truncate, $count)
    {
        $seeder = new UserTableSeeder();
        $seeder->callWith(UserTableSeeder::class, [$truncate, $count]);
    }

    private function runRoleTableSeederInteractive()
    {
        $this->info('Starting RoleTableSeeder');
        $randomPermission = true;
        $count = $this->ask('How many data:', 5);

        $this->info('Seeding...');

        $this->runRoleTableSeeder($randomPermission, $count);

        $this->info('RoleTableSeeder Finish.');
    }

    private function runRoleTableSeeder($randomPermission, $count)
    {
        $seeder = new RoleTableSeeder();
        $seeder->callWith(RoleTableSeeder::class, [$randomPermission, $count]);
    }

    private function runCompanyTableSeederInteractive()
    {
        $this->info('Starting CompanyTableSeeder');
        $companiesPerUsers = $this->ask('How many companies for each users:', 3);
        $userId = $this->ask('Only to this userId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runCompanyTableSeeder($companiesPerUsers, $userId);

        $this->info('CompanyTableSeeder Finish.');
    }

    private function runCompanyTableSeeder($companiesPerUsers, $userId)
    {
        $seeder = new CompanyTableSeeder();
        $seeder->callWith(CompanyTableSeeder::class, [$companiesPerUsers, $userId]);
    }

    private function runBranchTableSeederInteractive()
    {
        $this->info('Starting BranchTableSeeder');
        $branchPerCompanies = $this->ask('How many branches per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runBranchTableSeeder($branchPerCompanies, $onlyThisCompanyId);

        $this->info('BranchTableSeeder Finish.');
    }

    private function runBranchTableSeeder($branchPerCompanies, $onlyThisCompanyId)
    {
        $seeder = new BranchTableSeeder();
        $seeder->callWith(BranchTableSeeder::class, [$branchPerCompanies, $onlyThisCompanyId]);
    }

    private function runEmployeeTableSeederInteractive()
    {
        $this->info('Starting EmployeeTableSeeder');
        $employeePerCompanies = $this->ask('How many employees per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);
        $onlyThisBranchId = $this->ask('Only for this branchId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runEmployeeTableSeeder($employeePerCompanies, $onlyThisCompanyId, $onlyThisBranchId);

        $this->info('EmployeeTableSeeder Finish.');
    }

    private function runEmployeeTableSeeder($employeePerCompanies, $onlyThisCompanyId, $onlyThisBranchId)
    {
        $seeder = new EmployeeTableSeeder();
        $seeder->callWith(EmployeeTableSeeder::class, [$employeePerCompanies, $onlyThisCompanyId, $onlyThisBranchId]);
    }

    private function runWarehouseTableSeederInteractive()
    {
        $this->info('Starting WarehouseTableSeeder');
        $warehousePerCompanies = $this->ask('How many warehouse per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runWarehouseTableSeeder($warehousePerCompanies, $onlyThisCompanyId);

        $this->info('WarehouseTableSeeder Finish.');
    }

    private function runWarehouseTableSeeder($warehousePerCompanies, $onlyThisCompanyId)
    {
        $seeder = new WarehouseTableSeeder();
        $seeder->callWith(WarehouseTableSeeder::class, [$warehousePerCompanies, $onlyThisCompanyId]);
    }

    private function runProductGroupTableSeederInteractive()
    {
        $this->info('Starting ProductGroupTableSeeder');
        $countPerCompany = $this->ask('How many product groups per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);
        $category = $this->ask('Product category (0 to all):', 0);

        $this->info('Seeding...');

        $this->runProductGroupTableSeeder($countPerCompany, $onlyThisCompanyId, $category);

        $this->info('ProductGroupTableSeeder Finish.');
    }

    private function runProductGroupTableSeeder($countPerCompany, $onlyThisCompanyId, $category)
    {
        $seeder = new ProductGroupTableSeeder();
        $seeder->callWith(ProductGroupTableSeeder::class, [$countPerCompany, $onlyThisCompanyId, $category]);
    }

    private function runBrandTableSeederInteractive()
    {
        $this->info('Starting BrandTableSeeder');
        $countPerCompany = $this->ask('How many brand per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runBrandTableSeeder($countPerCompany, $onlyThisCompanyId);

        $this->info('BrandTableSeeder Finish.');
    }

    private function runBrandTableSeeder($countPerCompany, $onlyThisCompanyId)
    {
        $seeder = new BrandTableSeeder();
        $seeder->callWith(BrandTableSeeder::class, [$countPerCompany, $onlyThisCompanyId]);
    }

    private function runUnitTableSeederInteractive()
    {
        $this->info('Starting UnitTableSeeder');
        $countPerCompany = $this->ask('How many unit per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);
        $$category = $this->ask('Category (0 to all):', 0);

        $this->info('Seeding...');

        $this->runUnitTableSeeder($countPerCompany, $onlyThisCompanyId, $category);

        $this->info('UnitTableSeeder Finish.');
    }

    private function runUnitTableSeeder($countPerCompany, $onlyThisCompanyId, $category)
    {
        $seeder = new UnitTableSeeder();
        $seeder->callWith(UnitTableSeeder::class, [$countPerCompany, $onlyThisCompanyId, $category]);
    }

    private function runProductTableSeederInteractive()
    {
        $this->info('Starting ProductTableSeeder');
        $productPerCompany = $this->ask('How many product per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);
        $category = $this->ask('Category (0 to all):', 0);

        $this->info('Seeding...');

        $this->runProductTableSeeder($productPerCompany, $onlyThisCompanyId, $category);

        $this->info('ProductTableSeeder Finish.');
    }

    private function runProductTableSeeder($productPerCompany, $onlyThisCompanyId, $category)
    {
        $seeder = new ProductTableSeeder();
        $seeder->callWith(ProductTableSeeder::class, [$productPerCompany, $onlyThisCompanyId, $category]);
    }

    private function runSupplierTableSeederInteractive()
    {
        $this->info('Starting SupplierTableSeeder');
        $supplierPerCompanies = $this->ask('How many supplier per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runSupplierTableSeeder($supplierPerCompanies, $onlyThisCompanyId);

        $this->info('SupplierTableSeeder Finish.');
    }

    private function runSupplierTableSeeder($supplierPerCompanies, $onlyThisCompanyId)
    {
        $seeder = new SupplierTableSeeder();
        $seeder->callWith(SupplierTableSeeder::class, [$supplierPerCompanies, $onlyThisCompanyId]);
    }

    private function runCustomerGroupTableSeederInteractive()
    {
        $this->info('Starting CustomerGroupTableSeeder');
        $countPerCompany = $this->ask('How many warehouse per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runCustomerGroupTableSeeder($countPerCompany, $onlyThisCompanyId);

        $this->info('CustomerGroupTableSeeder Finish.');
    }

    private function runCustomerGroupTableSeeder($countPerCompany, $onlyThisCompanyId)
    {
        $seeder = new CustomerGroupTableSeeder();
        $seeder->callWith(CustomerGroupTableSeeder::class, [$countPerCompany, $onlyThisCompanyId]);
    }
}
