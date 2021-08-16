<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\FinanceCashController;
use App\Http\Controllers\PurchaseSupplierController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesCustomerGroupController;
use App\Http\Controllers\SalesCustomerController;

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

        /* ext */

        Route::group(['prefix' => 'company'], function () {
            Route::get('read', [CompanyController::class, 'read'])->name('api.get.admin.company.read');
            Route::get('read/all/active', [CompanyController::class, 'getAllActiveCompany'])->name('api.get.admin.company.read.all_active');
        });

        Route::group(['prefix' => 'branch'], function () {
            Route::get('read', [BranchController::class, 'read'])->name('api.get.admin.branch.read');
            Route::get('permissions/read', [BranchController::class, 'getAllPermissions'])->name('api.get.admin.branch.permissions.read');
        });

        Route::group(['prefix' => 'warehouse'], function () {
            Route::get('read', [WarehouseController::class, 'read'])->name('api.get.admin.warehouse.read');
            Route::get('permissions/read', [WarehouseController::class, 'getAllPermissions'])->name('api.get.admin.warehouse.permissions.read');
        });

        Route::group(['prefix' => 'cash'], function () {
            Route::get('read', [FinanceCashController::class, 'read'])->name('api.get.admin.financecash.read');
            Route::get('permissions/read', [FinanceCashController::class, 'getAllPermissions'])->name('api.get.admin.financecash.permissions.read');
        });

        Route::group(['prefix' => 'supplier'], function () {
            Route::get('read', [PurchaseSupplierController::class, 'read'])->name('api.get.admin.purchasesupplier.read');
            Route::get('permissions/read', [PurchaseSupplierController::class, 'getAllPermissions'])->name('api.get.admin.purchasesupplier.permissions.read');
        });

        Route::group(['prefix' => 'productgroup'], function () {
            Route::get('read', [ProductGroupController::class, 'read'])->name('api.get.admin.productgroup.read');
            Route::get('permissions/read', [ProductGroupController::class, 'getAllPermissions'])->name('api.get.admin.productgroup.permissions.read');
        });

        Route::group(['prefix' => 'productbrand'], function () {
            Route::get('read', [ProductBrandController::class, 'read'])->name('api.get.admin.productbrand.read');
            Route::get('permissions/read', [ProductBrandController::class, 'getAllPermissions'])->name('api.get.admin.productbrand.permissions.read');
        });

        Route::group(['prefix' => 'productunit'], function () {
            Route::get('read', [ProductUnitController::class, 'read'])->name('api.get.admin.productunit.read');
            Route::get('permissions/read', [ProductUnitController::class, 'getAllPermissions'])->name('api.get.admin.productunit.permissions.read');
        });

        Route::group(['prefix' => 'product'], function () {
            Route::get('read', [ProductController::class, 'read'])->name('api.get.admin.product.read');
            Route::get('permissions/read', [ProductController::class, 'getAllPermissions'])->name('api.get.admin.product.permissions.read');
        });

        Route::group(['prefix' => 'customergroup'], function () {
            Route::get('read', [SalesCustomerGroupController::class, 'read'])->name('api.get.admin.customergroup.read');
            Route::get('permissions/read', [SalesCustomerGroupController::class, 'getAllPermissions'])->name('api.get.admin.customergroup.permissions.read');
        });

        Route::group(['prefix' => 'customer'], function () {
            Route::get('read', [SalesCustomerController::class, 'read'])->name('api.get.admin.customer.read');
            Route::get('permissions/read', [SalesCustomerController::class, 'getAllPermissions'])->name('api.get.admin.customer.permissions.read');
        });

        /* ext */
    });

    Route::group(['prefix' => 'common'], function () {
        Route::get('countries/read', [CommonController::class, 'getCountries'])->name('api.get.common.countries.read');
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

        /* ext */

        Route::group(['prefix' => 'company'], function () {
            Route::post('save', [CompanyController::class, 'store'])->name('api.post.admin.company.save');
            Route::post('edit/{id}', [CompanyController::class, 'update'])->name('api.post.admin.company.edit');
            Route::post('delete/{id}', [CompanyController::class, 'delete'])->name('api.post.admin.company.delete');
        });

        Route::group(['prefix' => 'branch'], function () {
            Route::post('save', [BranchController::class, 'store'])->name('api.post.admin.branch.save');
            Route::post('edit/{id}', [BranchController::class, 'update'])->name('api.post.admin.branch.edit');
            Route::post('delete/{id}', [BranchController::class, 'delete'])->name('api.post.admin.branch.delete');
        });

        Route::group(['prefix' => 'warehouse'], function () {
            Route::post('save', [WarehouseController::class, 'store'])->name('api.post.admin.warehouse.save');
            Route::post('edit/{id}', [WarehouseController::class, 'update'])->name('api.post.admin.warehouse.edit');
            Route::post('delete/{id}', [WarehouseController::class, 'delete'])->name('api.post.admin.warehouse.delete');
        });

        Route::group(['prefix' => 'cash'], function () {
            Route::post('save', [FinanceCashController::class, 'store'])->name('api.post.admin.cash.save');
            Route::post('edit/{id}', [FinanceCashController::class, 'update'])->name('api.post.admin.cash.edit');
            Route::post('delete/{id}', [FinanceCashController::class, 'delete'])->name('api.post.admin.cash.delete');
        });

        Route::group(['prefix' => 'supplier'], function () {
            Route::post('save', [PurchaseSupplierController::class, 'store'])->name('api.post.admin.supplier.save');
            Route::post('edit/{id}', [PurchaseSupplierController::class, 'update'])->name('api.post.admin.supplier.edit');
            Route::post('delete/{id}', [PurchaseSupplierController::class, 'delete'])->name('api.post.admin.supplier.delete');
        });
        
        Route::group(['prefix' => 'productgroup'], function () {
            Route::post('save', [ProductGroupController::class, 'store'])->name('api.post.admin.productgroup.save');
            Route::post('edit/{id}', [ProductGroupController::class, 'update'])->name('api.post.admin.productgroup.edit');
            Route::post('delete/{id}', [ProductGroupController::class, 'delete'])->name('api.post.admin.productgroup.delete');
        });

        Route::group(['prefix' => 'productbrand'], function () {
            Route::post('save', [ProductBrandController::class, 'store'])->name('api.post.admin.productbrand.save');
            Route::post('edit/{id}', [ProductBrandController::class, 'update'])->name('api.post.admin.productbrand.edit');
            Route::post('delete/{id}', [ProductBrandController::class, 'delete'])->name('api.post.admin.productbrand.delete');
        });

        Route::group(['prefix' => 'productunit'], function () {
            Route::post('save', [ProductUnitController::class, 'store'])->name('api.post.admin.productunit.save');
            Route::post('edit/{id}', [ProductUnitController::class, 'update'])->name('api.post.admin.productunit.edit');
            Route::post('delete/{id}', [ProductUnitController::class, 'delete'])->name('api.post.admin.productunit.delete');
        });

        Route::group(['prefix' => 'product'], function () {
            Route::post('save', [ProductController::class, 'store'])->name('api.post.admin.product.save');
            Route::post('edit/{id}', [ProductController::class, 'update'])->name('api.post.admin.product.edit');
            Route::post('delete/{id}', [ProductController::class, 'delete'])->name('api.post.admin.product.delete');
        });

        Route::group(['prefix' => 'customergroup'], function () {
            Route::post('save', [SalesCustomerGroupController::class, 'store'])->name('api.post.admin.customergroup.save');
            Route::post('edit/{id}', [SalesCustomerGroupController::class, 'update'])->name('api.post.admin.customergroup.edit');
            Route::post('delete/{id}', [SalesCustomerGroupController::class, 'delete'])->name('api.post.admin.customergroup.delete');
        });

        Route::group(['prefix' => 'customer'], function () {
            Route::post('save', [SalesCustomerController::class, 'store'])->name('api.post.admin.customer.save');
            Route::post('edit/{id}', [SalesCustomerController::class, 'update'])->name('api.post.admin.customer.edit');
            Route::post('delete/{id}', [SalesCustomerController::class, 'delete'])->name('api.post.admin.customer.delete');
        });

        /* ext */
    });
});
