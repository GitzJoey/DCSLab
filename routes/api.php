<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiAuthController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerController;

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

Route::post('auth', [ApiAuthController::class, 'auth'])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => 'auth:sanctum'], function () {
    Route::get('profile/read', [DashboardController::class, 'readProfile'])->name('api.get.profile.read');

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

        Route::group(['prefix' => 'supplier'], function () {
            Route::get('read', [SupplierController::class, 'read'])->name('api.get.dashboard.supplier.read');
        });

        Route::group(['prefix' => 'productgroup'], function () {
            Route::get('read', [ProductGroupController::class, 'read'])->name('api.get.dashboard.productgroup.read');
            Route::get('read/all/active', [ProductGroupController::class, 'getAllActiveProductGroup'])->name('api.get.dashboard.productgroup.read.all_active');
        });

        Route::group(['prefix' => 'productbrand'], function () {
            Route::get('read', [ProductBrandController::class, 'read'])->name('api.get.dashboard.productbrand.read');
            Route::get('read/all/active', [ProductBrandController::class, 'getAllActiveProductBrand'])->name('api.get.dashboard.productbrand.read.all_active');
        });

        Route::group(['prefix' => 'unit'], function () {
            Route::get('read', [UnitController::class, 'read'])->name('api.get.dashboard.unit.read');
            Route::get('read/all/active', [UnitController::class, 'getAllActiveUnit'])->name('api.get.dashboard.unit.read.all_active');
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

Route::group(['prefix' => 'post', 'middleware' => 'auth:sanctum'], function () {
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
        });

        Route::group(['prefix' => 'cash'], function () {
            Route::post('save', [CashController::class, 'store'])->name('api.post.dashboard.cash.save');
            Route::post('edit/{id}', [CashController::class, 'update'])->name('api.post.dashboard.cash.edit');
            Route::post('delete/{id}', [CashController::class, 'delete'])->name('api.post.dashboard.cash.delete');
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
