<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => ['guest', 'throttle:3,1']])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1'], 'as' => 'api.get'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
        /* #region Extensions */
        Route::group(['prefix' => 'company', 'as' => '.company'], function () {
            Route::group(['prefix' => 'company', 'as' => '.company'], function () {
                Route::get('read', [CompanyController::class, 'readAny'])->name('.read_any');
                Route::get('read/{company:ulid}', [CompanyController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'branch', 'as' => '.branch'], function () {
                Route::get('read', [BranchController::class, 'readAny'])->name('.read_any');
                Route::get('read/{branch:ulid}', [BranchController::class, 'read'])->name('.read');
            });
        });
        /* #endregion */

        Route::group(['prefix' => 'admin', 'as' => '.admin'], function () {
            Route::group(['prefix' => 'user', 'as' => '.user'], function () {
                Route::get('read', [UserController::class, 'readAny'])->name('.read_any');
                Route::get('read/{user:ulid}', [UserController::class, 'read'])->name('.read');

                Route::get('read/{user:ulid}/tokens/count', [UserController::class, 'getTokensCount'])->name('.read.tokens.count');
            });

            Route::group(['prefix' => 'role', 'as' => '.role'], function () {
                Route::get('read', [RoleController::class, 'readAny'])->name('.read_any');
            });
        });

        Route::group(['prefix' => 'core', 'as' => '.core'], function () {
            Route::get('user/menu', [DashboardController::class, 'userMenu'])->name('.user.menu');
            Route::get('user/api', [DashboardController::class, 'userApi'])->name('.user.api');
        });

        Route::group(['prefix' => 'common', 'as' => '.common'], function () {
            Route::group(['prefix' => 'ddl', 'as' => '.ddl'], function () {
                Route::get('list/countries', [CommonController::class, 'getCountries'])->name('.list.countries');
                Route::get('list/statuses', [CommonController::class, 'getStatus'])->name('.list.statuses');
            });
        });

        Route::group(['prefix' => 'module', 'as' => '.module'], function () {
            Route::group(['prefix' => 'profile', 'as' => '.profile'], function () {
                Route::get('read', [ProfileController::class, 'readProfile'])->name('.read');

                Route::get('2fa/status', [ProfileController::class, 'checkTwoFAStatus'])->name('.2fa.status');
            });
        });
    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth:sanctum', 'throttle:50,1'], 'as' => 'api.post'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
        /* #region Extensions */
        Route::group(['prefix' => 'company', 'middleware' => ['precognitive'], 'as' => '.company'], function () {
            Route::group(['prefix' => 'company', 'as' => '.company'], function () {
                Route::post('save', [CompanyController::class, 'store'])->name('.save');
                Route::post('edit/{company:ulid}', [CompanyController::class, 'update'])->name('.edit');
                Route::post('delete/{company:ulid}', [CompanyController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'branch', 'middleware' => ['precognitive'], 'as' => '.branch'], function () {
                Route::post('save', [BranchController::class, 'store'])->name('.save');
                Route::post('edit/{branch:ulid}', [BranchController::class, 'update'])->name('.edit');
                Route::post('delete/{branch:ulid}', [BranchController::class, 'delete'])->name('.delete');
            });
        });
        /* #endregion */

        Route::group(['prefix' => 'admin', 'as' => '.admin'], function () {
            Route::group(['prefix' => 'user', 'middleware' => ['precognitive'], 'as' => '.user'], function () {
                Route::post('save', [UserController::class, 'store'])->name('.save');
                Route::post('edit/{user:ulid}', [UserController::class, 'update'])->name('.edit');
            });
        });

        Route::group(['prefix' => 'core', 'middleware' => ['precognitive'], 'as' => '.core'], function () {
            Route::post('user/upload', [DashboardController::class, 'userUpload'])->name('.user.upload');
        });

        Route::group(['prefix' => 'module', 'as' => '.module'], function () {
            Route::group(['prefix' => 'profile', 'middleware' => ['precognitive'], 'as' => '.profile'], function () {
                Route::post('update/user', [ProfileController::class, 'updateUser'])->name('.update.user');
                Route::post('update/profile', [ProfileController::class, 'updateProfile'])->name('.update.profile');
                Route::post('update/roles', [ProfileController::class, 'updateRoles'])->name('.update.roles');
                Route::post('update/setting', [ProfileController::class, 'updateSettings'])->name('.update.setting');

                Route::post('update/password', [ProfileController::class, 'changePassword'])->name('.update.password');

                Route::post('send/verification', [ProfileController::class, 'sendEmailVerification'])->name('.send.email_verification');
            });
        });
    });
});
