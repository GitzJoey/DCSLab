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
    ->middleware(['guest'])
    ->name('api.auth');

Route::middleware(['auth:sanctum'])
    ->prefix('dashboard')
    ->middleware([SetLocale::class, ValidateUser::class, XssSanitizer::class])
    ->as('api.dashboard.')
    ->group(function () {

        Route::prefix('company')->as('company.')->group(function () {

            Route::prefix('company')->as('company.')->group(function () {
                Route::get('index', [CompanyController::class, 'index'])->name('index');

                Route::middleware([HandlePrecognitiveRequests::class])->group(function () {
                    Route::post('store', [CompanyController::class, 'store'])->name('store');
                    Route::patch('update/{company:ulid}', [CompanyController::class, 'update'])->name('update');
                    Route::delete('destroy/{company:ulid}', [CompanyController::class, 'destroy'])->name('destroy');
                });
            });

            Route::prefix('branch')->as('branch.')->group(function () {
                Route::get('index', [BranchController::class, 'index'])->name('index');

                Route::middleware([HandlePrecognitiveRequests::class])->group(function () {
                    Route::post('store', [BranchController::class, 'store'])->name('store');
                    Route::patch('update/{branch:ulid}', [BranchController::class, 'update'])->name('update');
                    Route::delete('destroy/{branch:ulid}', [BranchController::class, 'destroy'])->name('destroy');
                });
            });
        });

        Route::prefix('admin')->as('admin.')->group(function () {

            Route::prefix('user')->as('user.')->group(function () {
                Route::get('index', [UserController::class, 'index'])->name('index');
                Route::get('show/{user:ulid}', [UserController::class, 'show'])->name('show');
                Route::get('show/{user:ulid}/tokens/count', [UserController::class, 'getTokensCount'])->name('show.tokens.count');

                Route::middleware([HandlePrecognitiveRequests::class])->group(function () {
                    Route::post('store', [UserController::class, 'store'])->name('store');
                    Route::patch('update/{user:ulid}', [UserController::class, 'update'])->name('update');
                });
            });

            Route::prefix('role')->as('role.')->group(function () {
                Route::get('index', [RoleController::class, 'index'])->name('index');
            });
        });

        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('', [ProfileController::class, 'show'])->name('show');

            Route::middleware([HandlePrecognitiveRequests::class])->group(function () {
                Route::patch('update/user_profile', [ProfileController::class, 'updateUserProfile'])->name('update.user_profile');
                Route::patch('update/personal_info', [ProfileController::class, 'updatePersonalInformation'])->name('update.personal_info');
                Route::patch('update/account_settings', [ProfileController::class, 'updateAccountSettings'])->name('update.account_settings');
                Route::patch('update/roles', [ProfileController::class, 'updateUserRoles'])->name('update.roles');
                Route::patch('update/password', [ProfileController::class, 'updatePassword'])->name('update.password');
                Route::patch('update/tokens', [ProfileController::class, 'updateTokens'])->name('update.tokens');
                Route::post('send/verification', [ProfileController::class, 'sendEmailVerification'])->name('send.email_verification');
            });
        });

        Route::prefix('common')->as('common.')->group(function () {
            Route::prefix('ddl')->as('ddl.')->group(function () {
                Route::get('list/countries', [CommonController::class, 'getCountries'])->name('list.countries');
                Route::get('list/statuses', [CommonController::class, 'getStatus'])->name('list.statuses');
            });
        });

        Route::get('menu', [DashboardController::class, 'menu'])->name('menu');
        Route::get('routes', [DashboardController::class, 'routes'])->name('routes');
        Route::get('search', [SearchController::class, 'search'])->name('search');

        Route::post('upload', [DashboardController::class, 'userUpload'])
            ->middleware([HandlePrecognitiveRequests::class])
            ->name('upload');
    });
