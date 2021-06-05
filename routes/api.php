<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return \Vinkla\Hashids\Facades\Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::group(['prefix' => 'get', 'middleware' => 'auth:sanctum'], function () {
    Route::get('profile/read', [DashboardController::class, 'readProfile'])->name('api.get.profile.read');

    Route::group(['prefix' => 'inbox'], function () {
        Route::get('read', [InboxController::class, 'readThread'])->name('api.get.inbox.thread.read');
        Route::get('user/list/read', [InboxController::class, 'getUserList'])->name('api.get.inbox.user.read');
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::group(['prefix' => 'role'], function () {
            Route::get('read', [RoleController::class, 'read'])->name('api.get.admin.role.read');
            Route::get('permissions/read', [RoleController::class, 'getAllPermissions'])->name('api.get.admin.role.permissions.read');
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('read', [UserController::class, 'read'])->name('api.get.admin.user.read');
            Route::get('read/by/{id}', [UserController::class, 'readCreatedById'])->name('api.get.admin.user.read.by.id');

            Route::get('roles/read', [UserController::class, 'getAllRoles'])->name('api.get.admin.user.roles.read');
        });
    });

    Route::group(['prefix' => 'common'], function () {
        Route::get('countries/read', [CommonController::class, 'getCountries'])->name('api.get.common.countries.read');
    });
});

Route::group(['prefix' => 'post', 'middleware' => 'auth:sanctum'], function () {
    Route::post('profile/update', [DashboardController::class, 'updateProfile'])->name('api.post.profile.update');

    Route::group(['prefix' => 'inbox'], function () {
        Route::post('save', [InboxController::class, 'store'])->name('api.get.inbox.thread.save');
        Route::post('edit/id', [InboxController::class, 'update'])->name('api.get.inbox.user.edit');
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::group(['prefix' => 'role'], function () {
            Route::post('save', [RoleController::class, 'store'])->name('api.post.admin.role.save');
            Route::post('edit/{id}', [RoleController::class, 'update'])->name('api.post.admin.role.edit');
        });

        Route::group(['prefix' => 'user'], function () {
            Route::post('save', [UserController::class, 'store'])->name('api.post.admin.user.save');
            Route::post('edit/{id}', [UserController::class, 'update'])->name('api.post.admin.user.edit');
            Route::post('reset/pswd/{id}', [UserController::class, 'resetPassword'])->name('api.post.admin.user.reset_pwd');
        });
    });
});
