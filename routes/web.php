<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DevController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\FinanceCashController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesCustomerController;
use App\Http\Controllers\SalesCustomerGroupController;

use Vinkla\Hashids\Facades\Hashids;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/home', function() {
    return view('home');
})->name('home');

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::group(['prefix' => LaravelLocalization::setLocale()], function() {
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('', [DashboardController::class, 'index'])->name('db');
        Route::get('profile', [DashboardController::class, 'profile'])->name('db.profile');
        Route::get('inbox', [InboxController::class, 'index'])->name('db.inbox');
        Route::get('activity', [ActivityLogController::class, 'index'])->name('db.activity');

        Route::get('user/verify/email', [UserController::class, 'sendEmailVerification'])->name('db.user.verify_email');
        Route::get('user/email/verify/{id}/{hash}', [UserController::class, 'emailVerification'])->name('verification.verify');

        Route::get('logs', ['middleware' => ['role:dev'], 'uses' => '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index'])->name('db.logs');

        /* ext */

        Route::group(['prefix' => 'product'], function () {
            Route::get('group', [ProductGroupController::class, 'index'])->name('db.product_groups');
            Route::get('brand', [ProductBrandController::class, 'index'])->name('db.product_brands');
            Route::get('unit', [ProductUnitController::class, 'index'])->name('db.product_units');
            Route::get('products', [ProductController::class, 'index'])->name('db.products');

        });

        Route::group(['prefix' => 'company'], function () {
            Route::get('companies', [CompanyController::class, 'index'])->name('db.company.companies');
            Route::get('branches', [BranchController::class, 'index'])->name('db.company.branches');
            Route::get('warehouses', [WarehouseController::class, 'index'])->name('db.company.warehouses');
        });

        Route::group(['prefix' => 'finance'], function () {
            Route::get('cashes', [FinanceCashController::class, 'index'])->name('db.finance_cashes');
        });

        Route::group(['prefix' => 'sales'], function () {
            Route::get('customer groups', [SalesCustomerGroupController::class, 'index'])->name('db.sales_customer_groups');
            Route::get('customers', [SalesCustomerController::class, 'index'])->name('db.sales_customers');
        });

        /* ext */

        Route::group(['prefix' => 'admin', 'middleware' => ['role:administrator|dev']],function() {
            Route::group(['prefix' => 'users'],function() {
                Route::get('users', [UserController::class, 'index'])->name('db.admin.users.users');
                Route::get('roles', [RoleController::class, 'index'])->name('db.admin.users.roles');
            });
        });

        Route::group(['prefix' => 'dev', 'middleware' => ['role:dev']],function() {
            Route::group(['prefix' => 'tools'],function() {
                Route::get('db_backup', [DevController::class, 'db_backup'])->name('db.dev.tools.db_backup');
            });

            Route::group(['prefix' => 'examples'],function() {
                Route::get('ex1', [DevController::class, 'ex1'])->name('db.dev.examples.ex1');
                Route::get('ex2', [DevController::class, 'ex2'])->name('db.dev.examples.ex2');
                Route::get('ex3', [DevController::class, 'ex3'])->name('db.dev.examples.ex3');
                Route::get('ex4', [DevController::class, 'ex4'])->name('db.dev.examples.ex4');
                Route::get('ex5', [DevController::class, 'ex5'])->name('db.dev.examples.ex5');
            });
        });
    });
});
