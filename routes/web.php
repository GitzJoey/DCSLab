<?php

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InboxController;


use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\CapitalGroupController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;

use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DoctorAccountingPageController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseGroupController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\IncomeGroupController;
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

        Route::get('activate/{system}', [DashboardController::class, 'activateSystem'])->name('db.activate');

        /* ext */

        Route::group(['prefix' => 'product'], function () {
            Route::get('group', [ProductGroupController::class, 'index'])->name('db.product.product_groups');
            Route::get('brand', [BrandController::class, 'index'])->name('db.product.brands');
            Route::get('unit', [UnitController::class, 'index'])->name('db.product.units');
            Route::get('products', [ProductController::class, 'index_product'])->name('db.product.products');
            Route::get('services', [ProductController::class, 'index_service'])->name('db.product.services');
        });

        Route::group(['prefix' => 'company'], function () {
            Route::get('companies', [CompanyController::class, 'index'])->name('db.company.companies');
            Route::get('employees', [EmployeeController::class, 'index'])->name('db.company.employees');
            Route::get('branches', [BranchController::class, 'index'])->name('db.company.branches');
            Route::get('warehouses', [WarehouseController::class, 'index'])->name('db.company.warehouses');

            Route::get('switch/company/{id}', [CompanyController::class, 'switchCompany'])->name('db.company.switch_company');
        });

        Route::group(['prefix' => 'finance'], function () {
            Route::get('chart_of_accounts', [ChartOfAccountController::class, 'index'])->name('db.finance.chart_of_accounts');
            Route::get('cashes', [CashController::class, 'index'])->name('db.finance.cashes');
            Route::get('investors', [InvestorController::class, 'index'])->name('db.finance.capital.investors');
            Route::get('capitals', [CapitalController::class, 'index'])->name('db.finance.capital.capitals');
            Route::get('capital_groups', [CapitalGroupController::class, 'index'])->name('db.finance.capital.capital_groups');
            Route::get('expense_groups', [ExpenseGroupController::class, 'index'])->name('db.finance.expense.expense_groups');
            Route::get('expenses', [ExpenseController::class, 'index'])->name('db.finance.expense.expenses');
            Route::get('income_groups', [IncomeGroupController::class, 'index'])->name('db.finance.income.income_groups');
            Route::get('incomes', [IncomeController::class, 'index'])->name('db.finance.income.incomes');
        });

        Route::group(['prefix' => 'purchase'], function () {
            Route::get('suppliers', [SupplierController::class, 'index'])->name('db.purchase.suppliers');
        });

        Route::group(['prefix' => 'sales'], function () {
            Route::get('customer groups', [CustomerGroupController::class, 'index'])->name('db.sales.customer_groups');
            Route::get('customers', [CustomerController::class, 'index'])->name('db.sales.customers');
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

    Route::group(['prefix' => 'doctoraccounting'], function () {
        Route::get('', [DoctorAccountingPageController::class, 'home']);

        Route::get('dokumentasi', [DoctorAccountingPageController::class, 'dokumentasi']);

        Route::get('faq', [DoctorAccountingPageController::class, 'faq']);

        Route::get('download', [DoctorAccountingPageController::class, 'download']);

        Route::get('harga', [DoctorAccountingPageController::class, 'harga']);

        Route::get('client', [DoctorAccountingPageController::class, 'client']);

        Route::get('tentangkami', [DoctorAccountingPageController::class, 'tentangkami']);
    });

    Route::get('/doctoraccounting/daold', function() {
        return view('doctoraccounting/home_old', ['title' => 'Home']);
    });

});
