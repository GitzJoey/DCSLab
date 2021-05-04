<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DevController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceCashController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductUnitController;
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
        Route::get('settings', [DashboardController::class, 'settings'])->name('db.settings');

        Route::group(['prefix' => 'product'], function () {
            Route::get('group', [ProductGroupController::class, 'index'])->name('db.product_groups');
            Route::get('brand', [ProductBrandController::class, 'index'])->name('db.product_brands');
            Route::get('unit', [ProductUnitController::class, 'index'])->name('db.product_units');
        });

        Route::group(['prefix' => 'finance'], function () {
            Route::get('cashes', [FinanceCashController::class, 'index'])->name('db.finance_cashes');
        });

        Route::group(['prefix' => 'sales'], function () {
            Route::get('customer groups', [SalesCustomerGroupController::class, 'index'])->name('db.sales_customer_groups');
        });
        
        Route::get('logs', ['middleware' => ['role:dev'], 'uses' => '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index'])->name('db.logs');

        Route::group(['prefix' => 'admin', 'middleware' => ['role:admin|dev']],function() {
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
