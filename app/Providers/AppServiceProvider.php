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
use App\Services\CashService;
use App\Services\SupplierService;
use App\Services\ProductGroupService;
use App\Services\ProductBrandService;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use App\Services\CustomerGroupService;
use App\Services\CustomerService;

use App\Services\Impls\CompanyServiceImpl;
use App\Services\Impls\BranchServiceImpl;
use App\Services\Impls\WarehouseServiceImpl;
use App\Services\Impls\CashServiceImpl;
use App\Services\Impls\SupplierServiceImpl;
use App\Services\Impls\ProductGroupServiceImpl;
use App\Services\Impls\ProductBrandServiceImpl;
use App\Services\Impls\ProductServiceImpl;
use App\Services\Impls\ProductUnitServiceImpl;
use App\Services\Impls\CustomerGroupServiceImpl;
use App\Services\Impls\CustomerServiceImpl;

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

        $this->app->singleton(CashService::class, function (){
            return new CashServiceImpl();
        });

        $this->app->singleton(CustomerGroupService::class, function (){
            return new CustomerGroupServiceImpl();
        });

        $this->app->singleton(CompanyService::class, function (){
            return new CompanyServiceImpl();
        });

        $this->app->singleton(CustomerService::class, function (){
            return new CustomerServiceImpl();
        });

        $this->app->singleton(BranchService::class, function (){
            return new BranchServiceImpl();
        });

        $this->app->singleton(WarehouseService::class, function (){
            return new WarehouseServiceImpl();
        });

        $this->app->singleton(SupplierService::class, function (){
            return new SupplierServiceImpl();
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
