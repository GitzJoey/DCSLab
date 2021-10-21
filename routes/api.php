<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return \Vinkla\Hashids\Facades\Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::post('/api/auth', [ApiAuthController::class, 'auth', 'middleware' => 'throttle:3,1'])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1'], 'as' => 'get'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => 'db'], function() {

        Route::group(['prefix' => 'admin', 'as' => 'admin'], function() {
            Route::group(['prefix' => 'users', 'as' => 'users'], function() {
                Route::get('read', [UserController::class, 'read'])->name('read');
            });
        });

        Route::group(['prefix' => 'core', 'as' => 'core'], function() {
            Route::get('user/profile', [DashboardController::class, 'userProfile'])->name('user_profile');
            Route::get('user/menu', [DashboardController::class, 'userMenu'])->name('user_menu');
        });

        Route::group(['prefix' => 'common', 'as' => 'common'], function() {
            Route::get('ddl/list/countries', [CommonController::class, 'getCountries'])->name('ddl.list.countries');
            Route::get('ddl/list/statuses', [CommonController::class, 'getStatus'])->name('ddl.list.statuses');
        });

    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth:sanctum','throttle:10,1'], 'as' => 'post'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => 'db'], function() {

        Route::group(['prefix' => 'core', 'as' => 'core'], function() {
            Route::post('log/route', [ActivityLogController::class, 'logRouteActivity'])->name('log_route');
        });
    });
});
