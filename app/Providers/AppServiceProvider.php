<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserService;
use App\Services\RoleService;

use App\Services\FinanceCashService;
use App\Services\ProductGroupService;
use App\Services\ProductBrandService;
use App\Services\SalesCustomerGroupService;


use App\Services\Impls\UserServiceImpl;
use App\Services\Impls\RoleServiceImpl;

use App\Services\Impls\FinanceCashServiceImpl;
use App\Services\Impls\ProductGroupServiceImpl;
use App\Services\Impls\ProductBrandServiceImpl;
use App\Services\Impls\SalesCustomerGroupServiceImpl;

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

        $this->app->singleton(FinanceCashService::class, function (){
            return new FinanceCashServiceImpl();
        });

        $this->app->singleton(SalesCustomerGroupService::class, function (){
            return new SalesCustomerGroupServiceImpl();
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
