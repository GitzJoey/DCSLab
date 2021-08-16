<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserService;
use App\Services\RoleService;
use App\Services\InboxService;
use App\Services\SystemService;
use App\Services\ActivityLogService;
use App\Services\Impls\UserServiceImpl;
use App\Services\Impls\RoleServiceImpl;
use App\Services\Impls\InboxServiceImpl;
use App\Services\Impls\SystemServiceImpl;
use App\Services\Impls\ActivityLogServiceImpl;

use App\Services\CompanyService;
use App\Services\BranchService;
use App\Services\WarehouseService;
use App\Services\FinanceCashService;
use App\Services\PurchaseSupplierService;
use App\Services\ProductGroupService;
use App\Services\ProductBrandService;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use App\Services\SalesCustomerGroupService;
use App\Services\SalesCustomerService;

use App\Services\Impls\CompanyServiceImpl;
use App\Services\Impls\BranchServiceImpl;
use App\Services\Impls\WarehouseServiceImpl;
use App\Services\Impls\FinanceCashServiceImpl;
use App\Services\Impls\PurchaseSupplierServiceImpl;
use App\Services\Impls\ProductGroupServiceImpl;
use App\Services\Impls\ProductBrandServiceImpl;
use App\Services\Impls\ProductServiceImpl;
use App\Services\Impls\ProductUnitServiceImpl;
use App\Services\Impls\SalesCustomerGroupServiceImpl;
use App\Services\Impls\SalesCustomerServiceImpl;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SystemService::class, function (){
            return new SystemServiceImpl();
        });

        $this->app->singleton(RoleService::class, function (){
            return new RoleServiceImpl();
        });

        $this->app->singleton(UserService::class, function (){
            return new UserServiceImpl();
        });

        $this->app->singleton(ActivityLogService::class, function (){
            return new ActivityLogServiceImpl();
        });

        $this->app->singleton(InboxService::class, function (){
            return new InboxServiceImpl();
        });

        $this->app->singleton(ProductGroupService::class, function (){
            return new ProductGroupServiceImpl();
        });

        $this->app->singleton(ProductBrandService::class, function (){
            return new ProductBrandServiceImpl();
        });

        $this->app->singleton(ProductUnitService::class, function (){
            return new ProductUnitServiceImpl();
        });

        $this->app->singleton(ProductService::class, function (){
            return new ProductServiceImpl();
        });

        $this->app->singleton(FinanceCashService::class, function (){
            return new FinanceCashServiceImpl();
        });

        $this->app->singleton(SalesCustomerGroupService::class, function (){
            return new SalesCustomerGroupServiceImpl();
        });

        $this->app->singleton(CompanyService::class, function (){
            return new CompanyServiceImpl();
        });

        $this->app->singleton(SalesCustomerService::class, function (){
            return new SalesCustomerServiceImpl();
        });

        $this->app->singleton(BranchService::class, function (){
            return new BranchServiceImpl();
        });

        $this->app->singleton(WarehouseService::class, function (){
            return new WarehouseServiceImpl();
        });

        $this->app->singleton(PurchaseSupplierService::class, function (){
            return new PurchaseSupplierServiceImpl();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
