<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

use App\Services\DashboardService;
use App\Services\UserService;
use App\Services\RoleService;
use App\Services\InboxService;
use App\Services\SystemService;
use App\Services\ActivityLogService;
/* Ext */
/* Ext */

use App\Services\Impls\DashboardServiceImpl;
use App\Services\Impls\UserServiceImpl;
use App\Services\Impls\RoleServiceImpl;
use App\Services\Impls\InboxServiceImpl;
use App\Services\Impls\SystemServiceImpl;
use App\Services\Impls\ActivityLogServiceImpl;
/* Ext */
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
