<?php

namespace App\Console\Commands;

use Database\Seeders\RoleTableSeeder;
use Database\Seeders\UserTableSeeder;
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
        
        switch (strtolower($this->argument('args'))) {
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
                break;
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
            case 'customer':
            case 'customertableseeder':
                $this->runCustomerTableSeederInteractive();
                break;
            default:
                $total = 12;
                $this->info('');
                $progressBar = $this->output->createProgressBar($total);
                $progressBar->start();

                $this->runUserTableSeeder(false, 5); $progressBar->advance();
                $this->runRoleTableSeeder(true, 5); $progressBar->advance();
                $this->runCompanyTableSeeder(5, 0); $progressBar->advance();
                $this->runBranchTableSeeder(5, 0); $progressBar->advance();
                $this->runEmployeeTableSeeder(5, 0, 0); $progressBar->advance();
                $this->runWarehouseTableSeeder(5, 0); $progressBar->advance();
                $this->runProductGroupTableSeeder(5, 0); $progressBar->advance();
                $this->runBrandTableSeeder(5, 0); $progressBar->advance();
                $this->runUnitTableSeeder(5, 0); $progressBar->advance();
                $this->runProductTableSeeder(5, 0); $progressBar->advance();
                $this->runSupplierTableSeeder(5, 0); $progressBar->advance();
                $this->runCustomerTableSeeder(5, 0); $progressBar->advance();
                
                $progressBar->finish();
                $this->info(''); $this->info('');
                break;
        }

        $this->info('Done!');
        return Command::SUCCESS;
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
        //$seeder = new CompanyTableSeeder();
        //$seeder->callWith(CompanyTableSeeder::class, [$companiesPerUsers, $userId]);
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
        //$seeder = new BranchTableSeeder();
        //$seeder->callWith(BranchTableSeeder::class, [$branchPerCompanies, $onlyThisCompanyId]);
    }

    private function runEmployeeTableSeederInteractive()
    {
        $this->info('Starting EmployeeTableSeeder');
        $count = $this->ask('How many employee for each branches in companies:', 5);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);
        $onlyThisBranchId = $this->ask('Only for this branchId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runEmployeeTableSeeder($count, $onlyThisCompanyId, $onlyThisBranchId);

        $this->info('CustomerTableSeeder Finish.');
    }
    
    private function runEmployeeTableSeeder($count, $onlyThisCompanyId, $onlyThisBranchId)
    {
        //$seeder = new EmployeeTableSeeder();
        //$seeder->callWith(EmployeeTableSeeder::class, [$count, $onlyThisCompanyId, $onlyThisBranchId]);
    }

    private function runWarehouseTableSeederInteractive()
    {
        $this->info('Starting WarehouseTableSeeder');
        $warehousePerCompanies = $this->ask('How many warehouses per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runWarehouseTableSeeder($warehousePerCompanies, $onlyThisCompanyId);

        $this->info('WarehouseTableSeeder Finish.');   
    }

    private function runWarehouseTableSeeder($warehousePerCompanies, $onlyThisCompanyId)
    {
        //$seeder = new WarehouseTableSeeder();
        //$seeder->callWith(WarehouseTableSeeder::class, [$warehousePerCompanies, $onlyThisCompanyId]);
    }

    private function runProductGroupTableSeederInteractive()
    {
        $this->info('Starting ProductGroupTableSeeder');
        $productGroupPerCompany = $this->ask('How many product groups (0 to skip):', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runProductGroupTableSeeder($productGroupPerCompany, $onlyThisCompanyId);

        $this->info('ProductGroupTableSeeder Finish.');   
    }

    private function runProductGroupTableSeeder($productGroupPerCompany, $onlyThisCompanyId)
    {
        //$seeder = new ProductGroupTableSeeder();
        //$seeder->callWith(ProductGroupTableSeeder::class, [$productGroupPerCompany, $onlyThisCompanyId]);
    }

    private function runBrandTableSeederInteractive()
    {
        $this->info('Starting BrandTableSeeder');
        $brandPerCompany = $this->ask('How many brands (0 to skip):', 5);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runBrandTableSeeder($brandPerCompany, $onlyThisCompanyId);

        $this->info('BrandTableSeeder Finish.');
    }

    private function runBrandTableSeeder($brandPerCompany, $onlyThisCompanyId)
    {
        //$seeder = new BrandTableSeeder();
        //$seeder->callWith(BrandTableSeeder::class, [$brandPerCompany, $onlyThisCompanyId]);
    }

    private function runUnitTableSeederInteractive()
    {
        $this->info('Starting UnitTableSeeder');
        $unitPerCompanies = $this->ask('How many units per company (0 to skip) :', 3);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runUnitTableSeeder($unitPerCompanies, $onlyThisCompanyId);

        $this->info('UnitTableSeeder Finish.');
    }

    private function runUnitTableSeeder($unitPerCompanies, $onlyThisCompanyId)
    {
        //$seeder = new UnitTableSeeder();
        //$seeder->callWith(UnitTableSeeder::class, [$unitPerCompanies, $onlyThisCompanyId]);
    }

    private function runProductTableSeederInteractive()
    {
        $this->info('Starting ProductTableSeeder');
        $productPerCompany = $this->ask('How many products for each companies:', 5);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runProductTableSeeder($productPerCompany, $onlyThisCompanyId);

        $this->info('ProductTableSeeder Finish.');
    }

    private function runProductTableSeeder($productPerCompany, $onlyThisCompanyId)
    {
        //$seeder = new ProductTableSeeder();
        //$seeder->callWith(ProductTableSeeder::class, [$productPerCompany, $onlyThisCompanyId]);
    }

    private function runSupplierTableSeederInteractive()
    {
        $this->info('Starting SupplierTableSeeder');
        $supplierPerCompany = $this->ask('How many supplier for each companies:', 5);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->info('Seeding...');

        $this->runSupplierTableSeeder($supplierPerCompany, $onlyThisCompanyId);

        $this->info('SupplierTableSeeder Finish.');
    }

    private function runSupplierTableSeeder($supplierPerCompany, $onlyThisCompanyId)
    {
        //$seeder = new SupplierTableSeeder();
        //$seeder->callWith(SupplierTableSeeder::class, [$supplierPerCompany, $onlyThisCompanyId]);
    }

    private function runCustomerTableSeederInteractive()
    {
        $this->info('Starting CustomerTableSeeder');
        $count = $this->ask('How many customer for each companies:', 5);
        $onlyThisCompanyId = $this->ask('Only for this companyId (0 to all):', 0);

        $this->runCustomerTableSeeder($count, $onlyThisCompanyId);

        $this->info('CustomerTableSeeder Finish.');
    }

    private function runCustomerTableSeeder($count, $onlyThisCompanyId)
    {
        //$seeder = new CustomerTableSeeder();
        //$seeder->callWith(CustomerTableSeeder::class, [$count, $onlyThisCompanyId]);
    }
}
