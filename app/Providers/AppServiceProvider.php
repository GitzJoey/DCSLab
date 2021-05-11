<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserService;
use App\Services\RoleService;

use App\Services\CompanyCompanyService;
use App\Services\CompanyBranchService;
use App\Services\CompanyWarehouseService;
use App\Services\FinanceCashService;
use App\Services\ProductGroupService;
use App\Services\ProductBrandService;
use App\Services\ProductProductService;
use App\Services\SalesCustomerGroupService;
use App\Services\SalesCustomerService;


use App\Services\Impls\UserServiceImpl;
use App\Services\Impls\RoleServiceImpl;

use App\Services\Impls\CompanyCompanyServiceImpl;
use App\Services\Impls\CompanyBranchServiceImpl;
use App\Services\Impls\CompanyWarehouseServiceImpl;
use App\Services\Impls\FinanceCashServiceImpl;
use App\Services\Impls\ProductGroupServiceImpl;
use App\Services\Impls\ProductBrandServiceImpl;
use App\Services\Impls\ProductProductServiceImpl;
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
        $this->app->singleton(RoleService::class, function (){
            return new RoleServiceImpl();
        });

        $this->app->singleton(UserService::class, function (){
            return new UserServiceImpl();
        });

        $this->app->singleton(ProductGroupService::class, function (){
            return new ProductGroupServiceImpl();
        });

        $this->app->singleton(ProductBrandService::class, function (){
            return new ProductBrandServiceImpl();
        });

        $this->app->singleton(ProductProductService::class, function (){
            return new ProductProductServiceImpl();
        });

        $this->app->singleton(FinanceCashService::class, function (){
            return new FinanceCashServiceImpl();
        });

        $this->app->singleton(SalesCustomerGroupService::class, function (){
            return new SalesCustomerGroupServiceImpl();
        });

        $this->app->singleton(CompanyCompanyService::class, function (){
            return new CompanyCompanyServiceImpl();
        });

        $this->app->singleton(SalesCustomerService::class, function (){
            return new SalesCustomerServiceImpl();
        });
        
        $this->app->singleton(CompanyBranchService::class, function (){
            return new CompanyBranchServiceImpl();
        });

        $this->app->singleton(CompanyWarehouseService::class, function (){
            return new CompanyWarehouseServiceImpl();
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
