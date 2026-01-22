<?php

namespace App\Console\Commands;

use Database\Seeders\BranchSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CashAccountSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CustomerGroupSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\InvestorSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StockAdjustmentCategorySeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class AppSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed';

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
                return Command::SUCCESS;
            }
        }

        $this->runDefault();

        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function runDefault()
    {
        $total = 11;
        $this->info('Starting data seeding...');
        $this->info('');
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        // (new UserSeeder())->run(truncate: false, count: 5);
        // $progressBar->advance();

        // (new RoleSeeder())->run(randomPermission: true, count: 5);
        // $progressBar->advance();

        (new CompanySeeder())->run(companiesPerUser: 1, userId: null);
        $progressBar->advance();

        (new BranchSeeder())->run(branchesPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new WarehouseSeeder())->run(warehousesPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new ProductCategorySeeder())->run(productCategoriesPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new BrandSeeder())->run(brandsPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new UnitSeeder())->run(unitsPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new StockAdjustmentCategorySeeder())->run(stockAdjustmentCategoriesPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new CashAccountSeeder())->run(cashAccountsPerCompany: 5, companyId: null);
        $progressBar->advance();

        // (new ProductSeeder())->run(companyId: null, qtyPerCompany: 5);
        // $progressBar->advance();

        (new CustomerGroupSeeder())->run(customerGroupsPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new CustomerSeeder())->run(customersPerCompany: 5, companyId: null);
        $progressBar->advance();

        (new InvestorSeeder())->run(investorsPerCompany: 5, companyId: null);
        $progressBar->advance();

        $progressBar->finish();
        $this->info('');
        $this->info('');
    }
}
