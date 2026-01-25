<?php

namespace App\Providers;

use App\Listeners\LoginEventListener;
use Illuminate\Support\Facades\Event;
use App\Listeners\LogoutEventListener;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            LoginEventListener::class,
            LogoutEventListener::class,
        );

        Schema::defaultStringLength(191);
    }
}
