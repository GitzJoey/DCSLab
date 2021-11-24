<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserService;
use App\Services\RoleService;
use App\Services\InboxService;
use App\Services\SystemService;
use App\Services\ActivityLogService;
/* Ext */
use App\Services\CompanyService;
/* Ext */

use App\Services\Impls\UserServiceImpl;
use App\Services\Impls\RoleServiceImpl;
use App\Services\Impls\InboxServiceImpl;
use App\Services\Impls\SystemServiceImpl;
use App\Services\Impls\ActivityLogServiceImpl;
/* Ext */
use App\Services\Impls\CompanyServiceImpl;
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
        /* Ext */
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
