<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ValidateUser;
use App\Http\Middleware\XssSanitizer;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::post('auth', [ApiAuthController::class, 'auth'])
    ->middleware(['guest', 'throttle:3,1'])
    ->name('api.auth');

Route::prefix('get')
    ->middleware(['auth:sanctum', 'throttle:100,1'])
    ->as('api.get.')
    ->group(function () {

        Route::prefix('dashboard')
            ->middleware([SetLocale::class, ValidateUser::class, XssSanitizer::class])
            ->as('dashboard.')
            ->group(function () {
                /* #region Extensions */
                Route::prefix('company')->as('company.')->group(function () {

                    Route::prefix('company')->as('company.')->group(function () {
                        Route::get('index', [CompanyController::class, 'index'])->name('index');
                    });

                    Route::prefix('branch')->as('branch.')->group(function () {
                        Route::get('index', [BranchController::class, 'index'])->name('index');
                    });

                });
                /* #endregion */

                Route::prefix('admin')->as('admin.')->group(function () {

                    Route::prefix('user')->as('user.')->group(function () {
                        Route::get('index', [UserController::class, 'index'])->name('index');
                        Route::get('show/{user:ulid}', [UserController::class, 'show'])->name('show');
                        Route::get('show/{user:ulid}/tokens/count', [UserController::class, 'getTokensCount'])->name('show.tokens.count');
                    });

                    Route::prefix('role')->as('role.')->group(function () {
                        Route::get('index', [RoleController::class, 'index'])->name('index');
                    });

                });

                Route::get('profile', [ProfileController::class, 'show'])->name('show');

                Route::get('menu', [DashboardController::class, 'userMenu'])->name('menu');
                Route::get('links', [DashboardController::class, 'userApi'])->name('links');
                
                Route::get('search', [SearchController::class, 'search'])->name('search');

                Route::prefix('common')->as('common.')->group(function () {
                    Route::prefix('ddl')->as('ddl.')->group(function () {
                        Route::get('list/countries', [CommonController::class, 'getCountries'])->name('list.countries');
                        Route::get('list/statuses', [CommonController::class, 'getStatus'])->name('list.statuses');
                    });
                });
            });
    });

Route::prefix('post')
    ->middleware(['auth:sanctum', 'throttle:50,1'])
    ->as('api.post.')
    ->group(function () {

        Route::prefix('dashboard')
            ->middleware([SetLocale::class, ValidateUser::class, XssSanitizer::class])
            ->as('dashboard.')
            ->group(function () {

                /* #region Extensions */
                Route::prefix('company')->as('company.')->group(function () {
                    Route::prefix('company')
                        ->middleware([HandlePrecognitiveRequests::class])
                        ->as('company.')
                        ->group(function () {
                            Route::post('store', [CompanyController::class, 'store'])->name('store');
                            Route::patch('update/{company:ulid}', [CompanyController::class, 'update'])->name('update');
                            Route::delete('destroy/{company:ulid}', [CompanyController::class, 'destroy'])->name('destroy');
                        });

                    Route::prefix('branch')
                        ->middleware([HandlePrecognitiveRequests::class])
                        ->as('branch.')
                        ->group(function () {
                            Route::post('store', [BranchController::class, 'store'])->name('store');
                            Route::patch('update/{branch:ulid}', [BranchController::class, 'update'])->name('update');
                            Route::delete('destroy/{branch:ulid}', [BranchController::class, 'destroy'])->name('destroy');
                        });

                });
                /* #endregion */

                Route::prefix('admin')->as('admin.')->group(function () {
                    Route::prefix('user')
                        ->middleware([HandlePrecognitiveRequests::class])
                        ->as('user.')
                        ->group(function () {
                            Route::post('store', [UserController::class, 'store'])->name('store');
                            Route::patch('update/{user:ulid}', [UserController::class, 'update'])->name('update');
                        });
                });

                Route::post('upload', [DashboardController::class, 'userUpload'])
                    ->middleware([HandlePrecognitiveRequests::class])
                    ->name('upload');

                Route::prefix('profile')
                    ->middleware([HandlePrecognitiveRequests::class])
                    ->as('profile.')
                    ->group(function () {
                        Route::patch('update/user_profile', [ProfileController::class, 'updateUserProfile'])->name('update.user_profile');
                        Route::patch('update/personal_info', [ProfileController::class, 'updatePersonalInformation'])->name('update.personal_info');
                        Route::patch('update/account_settings', [ProfileController::class, 'updateAccountSettings'])->name('update.account_settings');
                        Route::patch('update/roles', [ProfileController::class, 'updateUserRoles'])->name('update.roles');
                        Route::patch('update/password', [ProfileController::class, 'updatePassword'])->name('update.password');
                        Route::patch('update/tokens', [ProfileController::class, 'updateTokens'])->name('update.tokens');

                        Route::post('send/verification', [ProfileController::class, 'sendEmailVerification'])->name('send.email_verification');
                });

            });
    });
