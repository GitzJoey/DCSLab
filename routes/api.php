<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
/* Ext */
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\UnitController;
/* Ext */

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return \Vinkla\Hashids\Facades\Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => 'throttle:3,1'])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1'], 'as' => 'api.get'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function() {

        /* Ext */
        Route::group(['prefix' => 'company', 'as' => '.company'], function() {
            Route::group(['prefix' => 'company', 'as' => '.company'], function() {
                Route::get('read', [CompanyController::class, 'read'])->name('.read');

                Route::get('default', [CompanyController::class, 'getDefaultCompany'])->name('.default');
            });
            Route::group(['prefix' => 'branch', 'as' => '.branch'], function() {

            });
        });

        Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function() {
            Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function() {
                Route::get('read', [SupplierController::class, 'read'])->name('.read');
            });

            Route::group(['prefix' => 'common', 'as' => '.common'], function() {
                Route::get('list/payment_term', [SupplierController::class, 'getPaymentTermType'])->name('.list.payment_term');
            });
        });

        Route::group(['prefix' => 'product', 'as' => '.product'], function() {
            Route::group(['prefix' => 'brand', 'as' => '.brand'], function() {
                Route::get('read', [BrandController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'product_group', 'as' => '.product_group'], function() {
                Route::get('read', [ProductGroupController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'product', 'as' => '.product'], function() {
                Route::get('read', [ProductController::class, 'readProducts'])->name('.read');
            });
            Route::group(['prefix' => 'service', 'as' => '.service'], function() {
                Route::get('read', [ProductController::class, 'readServices'])->name('.read');
            });
            Route::group(['prefix' => 'unit', 'as' => '.unit'], function() {
                Route::get('read', [UnitController::class, 'read'])->name('.read');
            });

            Route::group(['prefix' => 'common', 'as' => '.common'], function() {
                Route::get('list/product_type', [ProductController::class, 'getProductType'])->name('.list.product_type');
            });
        });
        
        /* Ext */

        Route::group(['prefix' => 'admin', 'as' => '.admin'], function() {
            Route::group(['prefix' => 'users', 'as' => '.users'], function() {
                Route::get('read', [UserController::class, 'read'])->name('.read');

                Route::get('roles/read', [UserController::class, 'getAllRoles'])->name('.roles.read');
            });
        });

        Route::group(['prefix' => 'core', 'as' => '.core'], function() {
            Route::group(['prefix' => 'profile', 'as' => '.profile'], function() {
                Route::get('read', [ProfileController::class, 'readProfile'])->name('.read');
            });

            Route::group(['prefix' => 'inbox', 'as' => '.inbox'], function() {
                Route::get('list/threads', [InboxController::class, 'read'])->name('.list.thread');

                Route::get('search/users', [InboxController::class, 'searchUsers'])->name('.search.users');
            });

            Route::group(['prefix' => 'activity', 'as' => '.activity'], function() {
                Route::get('route/list', [ActivityLogController::class, 'getRouteActivity'])->name('.route.list');
            });

            Route::get('user/menu', [DashboardController::class, 'userMenu'])->name('.user_menu');
        });

        Route::group(['prefix' => 'common', 'as' => '.common'], function() {
            Route::group(['prefix' => 'ddl', 'as' => '.ddl'], function() {
                Route::get('list/countries', [CommonController::class, 'getCountries'])->name('.list.countries');
                Route::get('list/statuses', [CommonController::class, 'getStatus'])->name('.list.statuses');
            });

            Route::group(['prefix' => 'tools', 'as' => '.tools'], function() {
                Route::get('random/generator', [CommonController::class, 'getRandGenerator'])->name('.random.generator');
            });
        });
    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth:sanctum','throttle:50,1'], 'as' => 'api.post'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function() {

        /* Ext */
        Route::group(['prefix' => 'company', 'as' => '.company'], function() {
            Route::group(['prefix' => 'company', 'as' => '.company'], function() {
                Route::post('save', [CompanyController::class, 'store'])->name('.save');
                Route::post('edit/{id}', [CompanyController::class, 'update'])->name('.edit');
                Route::post('delete/{id}', [CompanyController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'branch', 'as' => '.branch'], function() {

            });
        });

        Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function() {
            Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function() {
                Route::post('save', [SupplierController::class, 'store'])->name('.save');
                Route::post('edit/{id}', [SupplierController::class, 'update'])->name('.edit');
                Route::post('delete/{id}', [SupplierController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'product', 'as' => '.product'], function() {
            Route::group(['prefix' => 'product', 'as' => '.product'], function() {
                Route::post('save', [ProductController::class, 'store'])->name('.save');
                Route::post('edit/{id}', [ProductController::class, 'update'])->name('.edit');
                Route::post('delete/{id}', [ProductController::class, 'delete'])->name('.delete');
            });
        });

        /* Ext */

        Route::group(['prefix' => 'admin', 'as' => '.admin'], function() {
            Route::group(['prefix' => 'users', 'as' => '.users'], function() {
                Route::post('save', [UserController::class, 'store'])->name('.save');
                Route::post('edit/{id}', [UserController::class, 'update'])->name('.edit');
            });
        });

        Route::group(['prefix' => 'core', 'as' => '.core'], function() {
            Route::group(['prefix' => 'profile', 'as' => '.profile'], function() {
                Route::group(['prefix' => 'update', 'as' => '.update'], function() {
                    Route::post('profile', [ProfileController::class, 'updateProfile'])->name('.profile');
                    Route::post('settings', [ProfileController::class, 'updateSettings'])->name('.settings');
                    Route::post('roles', [ProfileController::class, 'updateRoles'])->name('.roles');
                });

                Route::post('send/verification', [ProfileController::class, 'sendEmailVerification'])->name('.send_email_verification');
                Route::post('change/password', [ProfileController::class, 'changePassword'])->name('.change_password');
            });

            Route::group(['prefix' => 'inbox', 'as' => '.inbox'], function() {
                Route::post('save', [InboxController::class, 'store'])->name('.save');
                Route::post('edit', [InboxController::class, 'update'])->name('.edit');
            });

            Route::group(['prefix' => 'activity', 'as' => '.activity'], function() {
                Route::post('log/route', [ActivityLogController::class, 'logRouteActivity'])->name('.log_route');
            });
        });
    });
});
