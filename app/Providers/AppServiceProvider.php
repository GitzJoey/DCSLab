<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\RoleService;
use App\Services\Impls\RoleServiceImpl;

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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        return [
            'App\Services\RolesService',
        ];
    }
}
