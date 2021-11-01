<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiAuthController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\CapitalGroupController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseGroupController;
use App\Http\Controllers\IncomeGroupController;
use App\Http\Controllers\InvestorController;

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

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => 'throttle:3,1'])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1']], function () {
    Route::get('profile/read', [DashboardController::class, 'readProfile'])->name('api.get.profile.read');

    Route::get('menu', [DashboardController::class, 'menu'])->name('api.get.menu');

    Route::group(['prefix' => 'inbox'], function () {
        Route::get('read', [InboxController::class, 'read'])->name('api.get.inbox.thread.read');
        Route::get('show/{id}', [InboxController::class, 'show'])->name('api.get.inbox.thread.show');
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

    Route::group(['prefix' => 'dashboard'], function () {
        Route::group(['prefix' => 'company'], function () {
            Route::get('read', [CompanyController::class, 'read'])->name('api.get.dashboard.company.read');
            Route::get('read/all/active', [CompanyController::class, 'getAllActiveCompany'])->name('api.get.dashboard.company.read.all_active');
        });

        Route::group(['prefix' => 'employee'], function () {
            Route::get('read', [EmployeeController::class, 'read'])->name('api.get.dashboard.employee.read');
        });

        Route::group(['prefix' => 'branch'], function () {
            Route::get('read', [BranchController::class, 'read'])->name('api.get.dashboard.branch.read');
        });

        Route::group(['prefix' => 'warehouse'], function () {
            Route::get('read', [WarehouseController::class, 'read'])->name('api.get.dashboard.warehouse.read');
        });

        Route::group(['prefix' => 'cash'], function () {
            Route::get('read', [CashController::class, 'read'])->name('api.get.dashboard.cash.read');
            Route::get('read/all/active', [CashController::class, 'getAllActiveCash'])->name('api.get.dashboard.cash.read.all_active');
        });

        Route::group(['prefix' => 'investor'], function () {
            Route::get('read', [InvestorController::class, 'read'])->name('api.get.dashboard.investor.read');
            Route::get('read/all/active', [InvestorController::class, 'getAllActiveInvestor'])->name('api.get.dashboard.investor.read.all_active');
        });

        Route::group(['prefix' => 'capital'], function () {
            Route::get('read', [CapitalController::class, 'read'])->name('api.get.dashboard.capital.read');
        });

        Route::group(['prefix' => 'capitalgroup'], function () {
            Route::get('read', [CapitalGroupController::class, 'read'])->name('api.get.dashboard.capitalgroup.read');
            Route::get('read/all/active', [CapitalGroupController::class, 'getAllActiveCapitalGroup'])->name('api.get.dashboard.capitalgroup.read.all_active');
        });

        Route::group(['prefix' => 'expensegroup'], function () {
            Route::get('read', [ExpenseGroupController::class, 'read'])->name('api.get.dashboard.expensegroup.read');
        });

        Route::group(['prefix' => 'incomegroup'], function () {
            Route::get('read', [IncomeGroupController::class, 'read'])->name('api.get.dashboard.incomegroup.read');
        });

        Route::group(['prefix' => 'supplier'], function () {
            Route::get('read', [SupplierController::class, 'read'])->name('api.get.dashboard.supplier.read');
        });

        Route::group(['prefix' => 'productgroup'], function () {
            Route::get('read', [ProductGroupController::class, 'read'])->name('api.get.dashboard.productgroup.read');
            Route::get('read/all/', [ProductGroupController::class, 'getAllProductGroup'])->name('api.get.dashboard.productgroup.read.all');
        });

        Route::group(['prefix' => 'productbrand'], function () {
            Route::get('read', [ProductBrandController::class, 'read'])->name('api.get.dashboard.productbrand.read');
            Route::get('read/all/', [ProductBrandController::class, 'getAllProductBrand'])->name('api.get.dashboard.productbrand.read.all');
        });

        Route::group(['prefix' => 'productunit'], function () {
            Route::get('read', [ProductUnitController::class, 'read'])->name('api.get.dashboard.productunit.read');
            Route::get('read/all/', [ProductUnitController::class, 'getAllProductUnit'])->name('api.get.dashboard.productunit.read.all');
        });

        Route::group(['prefix' => 'unit'], function () {
            Route::get('read', [UnitController::class, 'read'])->name('api.get.dashboard.unit.read');
            Route::get('read/all/', [UnitController::class, 'getAllUnit'])->name('api.get.dashboard.unit.read.all');
        });

        Route::group(['prefix' => 'product'], function () {
            Route::get('read', [ProductController::class, 'read'])->name('api.get.dashboard.product.read');
        });

        Route::group(['prefix' => 'customergroup'], function () {
            Route::get('read', [CustomerGroupController::class, 'read'])->name('api.get.dashboard.customergroup.read');
            Route::get('read/all/active', [CustomerGroupController::class, 'getAllCustomerGroup'])->name('api.get.dashboard.customergroup.read.all_active');
        });

        Route::group(['prefix' => 'customer'], function () {
            Route::get('read', [CustomerController::class, 'read'])->name('api.get.dashboard.customer.read');
        });
    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth:sanctum','throttle:10,1']], function () {
    Route::post('profile/update', [DashboardController::class, 'updateProfile'])->name('api.post.profile.update');

    Route::group(['prefix' => 'inbox'], function () {
        Route::post('save', [InboxController::class, 'store'])->name('api.get.inbox.thread.save');
        Route::post('edit/{id}', [InboxController::class, 'update'])->name('api.get.inbox.user.edit');
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

    Route::group(['prefix' => 'dashboard'], function () {
        Route::group(['prefix' => 'company'], function () {
            Route::group(['prefix' => 'companies'], function () {
                Route::post('save', [CompanyController::class, 'store'])->name('api.post.dashboard.company.companies.save');
                Route::post('edit/{id}', [CompanyController::class, 'update'])->name('api.post.dashboard.company.companies.edit');
                Route::post('delete/{id}', [CompanyController::class, 'delete'])->name('api.post.dashboard.company.companies.delete');
            });

            Route::group(['prefix' => 'employees'], function () {
                Route::post('save', [EmployeeController::class, 'store'])->name('api.post.dashboard.company.employees.save');
                Route::post('edit/{id}', [EmployeeController::class, 'update'])->name('api.post.dashboard.company.employees.edit');
                Route::post('delete/{id}', [EmployeeController::class, 'delete'])->name('api.post.dashboard.company.employees.delete');
            });

            Route::group(['prefix' => 'branches'], function () {
                Route::post('save', [BranchController::class, 'store'])->name('api.post.dashboard.company.branches.save');
                Route::post('edit/{id}', [BranchController::class, 'update'])->name('api.post.dashboard.company.branches.edit');
                Route::post('delete/{id}', [BranchController::class, 'delete'])->name('api.post.dashboard.company.branches.delete');
            });

            Route::group(['prefix' => 'warehouses'], function () {
                Route::post('save', [WarehouseController::class, 'store'])->name('api.post.dashboard.company.warehouses.save');
                Route::post('edit/{id}', [WarehouseController::class, 'update'])->name('api.post.dashboard.company.warehouses.edit');
                Route::post('delete/{id}', [WarehouseController::class, 'delete'])->name('api.post.dashboard.company.warehouses.delete');
            });

            Route::group(['prefix' => 'cash'], function () {
                Route::post('save', [CashController::class, 'store'])->name('api.post.dashboard.cash.save');
                Route::post('edit/{id}', [CashController::class, 'update'])->name('api.post.dashboard.cash.edit');
                Route::post('delete/{id}', [CashController::class, 'delete'])->name('api.post.dashboard.cash.delete');
            });

            Route::group(['prefix' => 'investor'], function () {
                Route::post('save', [InvestorController::class, 'store'])->name('api.post.dashboard.investor.save');
                Route::post('edit/{id}', [InvestorController::class, 'update'])->name('api.post.dashboard.investor.edit');
                Route::post('delete/{id}', [InvestorController::class, 'delete'])->name('api.post.dashboard.investor.delete');
            });

            Route::group(['prefix' => 'capital'], function () {
                Route::post('save', [CapitalController::class, 'store'])->name('api.post.dashboard.capital.save');
                Route::post('edit/{id}', [CapitalController::class, 'update'])->name('api.post.dashboard.capital.edit');
                Route::post('delete/{id}', [CapitalController::class, 'delete'])->name('api.post.dashboard.capital.delete');
            });

            Route::group(['prefix' => 'capitalgroup'], function () {
                Route::post('save', [CapitalGroupController::class, 'store'])->name('api.post.dashboard.capitalgroup.save');
                Route::post('edit/{id}', [CapitalGroupController::class, 'update'])->name('api.post.dashboard.capitalgroup.edit');
                Route::post('delete/{id}', [CapitalGroupController::class, 'delete'])->name('api.post.dashboard.capitalgroup.delete');
            });

            Route::group(['prefix' => 'expensegroup'], function () {
                Route::post('save', [ExpenseGroupController::class, 'store'])->name('api.post.dashboard.expensegroup.save');
                Route::post('edit/{id}', [ExpenseGroupController::class, 'update'])->name('api.post.dashboard.expensegroup.edit');
                Route::post('delete/{id}', [ExpenseGroupController::class, 'delete'])->name('api.post.dashboard.expensegroup.delete');
            });

            Route::group(['prefix' => 'incomegroup'], function () {
                Route::post('save', [IncomeGroupController::class, 'store'])->name('api.post.dashboard.incomegroup.save');
                Route::post('edit/{id}', [IncomeGroupController::class, 'update'])->name('api.post.dashboard.incomegroup.edit');
                Route::post('delete/{id}', [IncomeGroupController::class, 'delete'])->name('api.post.dashboard.incomegroup.delete');
            });

            Route::group(['prefix' => 'supplier'], function () {
                Route::post('save', [SupplierController::class, 'store'])->name('api.post.dashboard.supplier.save');
                Route::post('edit/{id}', [SupplierController::class, 'update'])->name('api.post.dashboard.supplier.edit');
                Route::post('delete/{id}', [SupplierController::class, 'delete'])->name('api.post.dashboard.supplier.delete');
            });

            Route::group(['prefix' => 'productgroup'], function () {
                Route::post('save', [ProductGroupController::class, 'store'])->name('api.post.dashboard.productgroup.save');
                Route::post('edit/{id}', [ProductGroupController::class, 'update'])->name('api.post.dashboard.productgroup.edit');
                Route::post('delete/{id}', [ProductGroupController::class, 'delete'])->name('api.post.dashboard.productgroup.delete');
            });

            Route::group(['prefix' => 'productbrand'], function () {
                Route::post('save', [ProductBrandController::class, 'store'])->name('api.post.dashboard.productbrand.save');
                Route::post('edit/{id}', [ProductBrandController::class, 'update'])->name('api.post.dashboard.productbrand.edit');
                Route::post('delete/{id}', [ProductBrandController::class, 'delete'])->name('api.post.dashboard.productbrand.delete');
            });

            Route::group(['prefix' => 'productunit'], function () {
                Route::post('save', [ProductUnitController::class, 'store'])->name('api.post.dashboard.productunit.save');
                Route::post('edit/{id}', [ProductUnitController::class, 'update'])->name('api.post.dashboard.productunit.edit');
                Route::post('delete/{id}', [ProductUnitController::class, 'delete'])->name('api.post.dashboard.productunit.delete');
            });

            Route::group(['prefix' => 'unit'], function () {
                Route::post('save', [UnitController::class, 'store'])->name('api.post.dashboard.unit.save');
                Route::post('edit/{id}', [UnitController::class, 'update'])->name('api.post.dashboard.unit.edit');
                Route::post('delete/{id}', [UnitController::class, 'delete'])->name('api.post.dashboard.unit.delete');
            });

            Route::group(['prefix' => 'product'], function () {
                Route::post('save', [ProductController::class, 'store'])->name('api.post.dashboard.product.save');
                Route::post('edit/{id}', [ProductController::class, 'update'])->name('api.post.dashboard.product.edit');
                Route::post('delete/{id}', [ProductController::class, 'delete'])->name('api.post.dashboard.product.delete');
            });

            Route::group(['prefix' => 'customergroup'], function () {
                Route::post('save', [CustomerGroupController::class, 'store'])->name('api.post.dashboard.customergroup.save');
                Route::post('edit/{id}', [CustomerGroupController::class, 'update'])->name('api.post.dashboard.customergroup.edit');
                Route::post('delete/{id}', [CustomerGroupController::class, 'delete'])->name('api.post.dashboard.customergroup.delete');
            });

            Route::group(['prefix' => 'customer'], function () {
                Route::post('save', [CustomerController::class, 'store'])->name('api.post.dashboard.customer.save');
                Route::post('edit/{id}', [CustomerController::class, 'update'])->name('api.post.dashboard.customer.edit');
                Route::post('delete/{id}', [CustomerController::class, 'delete'])->name('api.post.dashboard.customer.delete');
            });
        });
    });
});
