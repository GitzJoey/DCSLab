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

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => ['guest', 'throttle:3,1']])->name('api.auth');

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
                        Route::get('view', [CompanyController::class, 'viewAny'])->name('view_any');
                        Route::get('view/{company:ulid}', [CompanyController::class, 'view'])->name('view');
                    });

                    Route::prefix('branch')->as('branch.')->group(function () {
                        Route::get('view', [BranchController::class, 'viewAny'])->name('view_any');
                        Route::get('view/{branch:ulid}', [BranchController::class, 'view'])->name('view');
                    });

                });
                /* #endregion */

                Route::prefix('admin')->as('admin.')->group(function () {

                    Route::prefix('user')->as('user.')->group(function () {
                        Route::get('view', [UserController::class, 'viewAny'])->name('view_any');
                        Route::get('view/{user:ulid}', [UserController::class, 'view'])->name('view');
                        Route::get('view/{user:ulid}/tokens/count', [UserController::class, 'getTokensCount'])->name('view.tokens.count');
                    });

                    Route::prefix('role')->as('role.')->group(function () {
                        Route::get('view', [RoleController::class, 'viewAny'])->name('view_any');
                    });

                });

                Route::prefix('core')->as('core.')->group(function () {
                    Route::get('user/menu', [DashboardController::class, 'userMenu'])->name('user.menu');
                    Route::get('user/api', [DashboardController::class, 'userApi'])->name('user.api');
                    Route::get('search', [SearchController::class, 'search'])->name('search');
                });

                Route::prefix('common')->as('common.')->group(function () {
                    Route::prefix('ddl')->as('ddl.')->group(function () {
                        Route::get('list/countries', [CommonController::class, 'getCountries'])->name('list.countries');
                        Route::get('list/statuses', [CommonController::class, 'getStatus'])->name('list.statuses');
                    });
                });

                Route::prefix('module')->as('module.')->group(function () {
                    Route::prefix('profile')->as('profile.')->group(function () {
                        Route::get('view', [ProfileController::class, 'viewProfile'])->name('view');
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
                            Route::post('create', [CompanyController::class, 'create'])->name('create');
                            Route::post('update/{company:ulid}', [CompanyController::class, 'update'])->name('update');
                            Route::post('delete/{company:ulid}', [CompanyController::class, 'delete'])->name('delete');
                        });

                    Route::prefix('branch')
                        ->middleware([HandlePrecognitiveRequests::class])
                        ->as('branch.')
                        ->group(function () {
                            Route::post('create', [BranchController::class, 'create'])->name('create');
                            Route::post('update/{branch:ulid}', [BranchController::class, 'update'])->name('update');
                            Route::post('delete/{branch:ulid}', [BranchController::class, 'delete'])->name('delete');
                        });

                });
                /* #endregion */

                Route::prefix('admin')->as('admin.')->group(function () {
                    Route::prefix('user')
                        ->middleware([HandlePrecognitiveRequests::class])
                        ->as('user.')
                        ->group(function () {
                            Route::post('create', [UserController::class, 'create'])->name('create');
                            Route::post('update/{user:ulid}', [UserController::class, 'update'])->name('update');
                        });
                });

                Route::prefix('core')
                    ->middleware([HandlePrecognitiveRequests::class])
                    ->as('core.')
                    ->group(function () {
                        Route::post('user/upload', [DashboardController::class, 'userUpload'])->name('user.upload');
                    });

                Route::prefix('module')->as('module.')->group(function () {
                    Route::prefix('profile')
                        ->middleware([HandlePrecognitiveRequests::class])
                        ->as('profile.')
                        ->group(function () {
                            Route::post('update/user_profile', [ProfileController::class, 'updateUserProfile'])->name('update.user_profile');
                            Route::post('update/personal_info', [ProfileController::class, 'updatePersonalInformation'])->name('update.personal_info');
                            Route::post('update/account_settings', [ProfileController::class, 'updateAccountSettings'])->name('update.account_settings');
                            Route::post('update/roles', [ProfileController::class, 'updateUserRoles'])->name('update.roles');
                            Route::post('update/password', [ProfileController::class, 'updatePassword'])->name('update.password');
                            Route::post('update/tokens', [ProfileController::class, 'updateTokens'])->name('update.tokens');

                            Route::post('send/verification', [ProfileController::class, 'sendEmailVerification'])->name('send.email_verification');
                        });
                });

            });
    });
