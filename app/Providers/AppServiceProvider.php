<?php

namespace App\Providers;

use App\Services\RoleService;
use App\Services\UnitService;
use App\Services\UserService;
use App\Services\BrandService;

use App\Services\InboxService;
use App\Services\BranchService;
use App\Services\SystemService;
use App\Services\CompanyService;
use App\Services\ProductService;
use App\Services\SupplierService;
/* Ext */
use App\Services\DashboardService;
use App\Services\WarehouseService;
use App\Services\ActivityLogService;
use App\Services\ProductGroupService;
use App\Services\Impls\RoleServiceImpl;
/* Ext */

use App\Services\Impls\UnitServiceImpl;
use App\Services\Impls\UserServiceImpl;
use Illuminate\Support\ServiceProvider;
use App\Services\Impls\BrandServiceImpl;
use App\Services\Impls\InboxServiceImpl;
use App\Services\Impls\BranchServiceImpl;
/* Ext */
use App\Services\Impls\SystemServiceImpl;
use App\Services\Impls\CompanyServiceImpl;
use App\Services\Impls\ProductServiceImpl;
use App\Services\Impls\SupplierServiceImpl;
use App\Services\Impls\DashboardServiceImpl;
use App\Services\Impls\WarehouseServiceImpl;
use App\Services\Impls\ActivityLogServiceImpl;
use App\Services\Impls\ProductGroupServiceImpl;
use Illuminate\Http\Resources\Json\JsonResource;

/* Ext */

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

        $this->app->singleton(DashboardService::class, function (){
            return new DashboardServiceImpl();
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

        /* Ext */
        
        $this->app->singleton(CompanyService::class, function (){
            return new CompanyServiceImpl();
        });

        $this->app->singleton(BranchService::class, function (){
            return new BranchServiceImpl();
        });

        $this->app->singleton(WarehouseService::class, function (){
            return new WarehouseServiceImpl();
        });

        $this->app->singleton(BrandService::class, function (){
            return new BrandServiceImpl();
        });

        $this->app->singleton(SupplierService::class, function (){
            return new SupplierServiceImpl();
        });

        $this->app->singleton(ProductService::class, function (){
            return new ProductServiceImpl();
        });

        $this->app->singleton(ProductGroupService::class, function (){
            return new ProductGroupServiceImpl();
        });

        $this->app->singleton(UnitService::class, function (){
            return new UnitServiceImpl();
        });

        /* Ext */
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
    }
}
