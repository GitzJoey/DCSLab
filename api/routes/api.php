<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CapitalOpeningController;
use App\Http\Controllers\CapitalTransactionController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseAdditionalCostCategoryController;
use App\Http\Controllers\PurchaseAdditionalCostController;
use App\Http\Controllers\PurchaseAdditionalCostPaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderDownPaymentController;
use App\Http\Controllers\PurchaseOrderDownPaymentRefundController;
use App\Http\Controllers\PurchaseOrderItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StockAdjustmentCategoryController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockAdjustmentInItemController;
use App\Http\Controllers\StockAdjustmentInItemSerialController;
use App\Http\Controllers\StockAdjustmentOutItemController;
use App\Http\Controllers\StockAdjustmentOutItemSerialController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockTransferItemController;
use App\Http\Controllers\StockTransferItemSerialController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatProfileController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => ['guest', 'throttle:3,1']])->name('api.auth');

Route::prefix('company')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.company.')->group(function () {
        Route::get('read', [CompanyController::class, 'readAny'])->name('read_any');
        Route::get('read/{company:ulid}', [CompanyController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.company.')->group(function () {
        Route::post('save', [CompanyController::class, 'store'])->name('save');
        Route::post('edit/{company:ulid}', [CompanyController::class, 'update'])->name('edit');
        Route::post('delete/{company:ulid}', [CompanyController::class, 'delete'])->name('delete');
    });
});

Route::prefix('branch')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.branch.')->group(function () {
        Route::get('read', [BranchController::class, 'readAny'])->name('read_any');
        Route::get('read/{branch:ulid}', [BranchController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.branch.')->group(function () {
        Route::post('save', [BranchController::class, 'store'])->name('save');
        Route::post('edit/{branch:ulid}', [BranchController::class, 'update'])->name('edit');
        Route::post('delete/{branch:ulid}', [BranchController::class, 'delete'])->name('delete');
    });
});

Route::prefix('warehouse')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.warehouse.')->group(function () {
        Route::get('read', [WarehouseController::class, 'readAny'])->name('read_any');
        Route::get('read/{warehouse:ulid}', [WarehouseController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.warehouse.')->group(function () {
        Route::post('save', [WarehouseController::class, 'store'])->name('save');
        Route::post('edit/{warehouse:ulid}', [WarehouseController::class, 'update'])->name('edit');
        Route::post('delete/{warehouse:ulid}', [WarehouseController::class, 'delete'])->name('delete');
    });
});

Route::prefix('investor')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.investor.')->group(function () {
        Route::get('read', [InvestorController::class, 'readAny'])->name('read_any');
        Route::get('read/{investor:ulid}', [InvestorController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.investor.')->group(function () {
        Route::post('save', [InvestorController::class, 'store'])->name('save');
        Route::post('edit/{investor:ulid}', [InvestorController::class, 'update'])->name('edit');
        Route::post('delete/{investor:ulid}', [InvestorController::class, 'delete'])->name('delete');
    });
});

Route::prefix('cash_account')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.cash_account.')->group(function () {
        Route::get('read', [CashAccountController::class, 'readAny'])->name('read_any');
        Route::get('read/{cash_account:ulid}', [CashAccountController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.cash_account.')->group(function () {
        Route::post('save', [CashAccountController::class, 'store'])->name('save');
        Route::post('edit/{cash_account:ulid}', [CashAccountController::class, 'update'])->name('edit');
        Route::post('delete/{cash_account:ulid}', [CashAccountController::class, 'delete'])->name('delete');
    });
});

Route::prefix('product_category')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.product_category.')->group(function () {
        Route::get('read', [ProductCategoryController::class, 'readAny'])->name('read_any');
        Route::get('read/types', [ProductCategoryController::class, 'getTypes'])->name('read_types');
        Route::get('read/{product_category:ulid}', [ProductCategoryController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.product_category.')->group(function () {
        Route::post('save', [ProductCategoryController::class, 'store'])->name('save');
        Route::post('edit/{product_category:ulid}', [ProductCategoryController::class, 'update'])->name('edit');
        Route::post('delete/{product_category:ulid}', [ProductCategoryController::class, 'delete'])->name('delete');
    });
});

Route::prefix('brand')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.brand.')->group(function () {
        Route::get('read', [BrandController::class, 'readAny'])->name('read_any');
        Route::get('read/{brand:ulid}', [BrandController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.brand.')->group(function () {
        Route::post('save', [BrandController::class, 'store'])->name('save');
        Route::post('edit/{brand:ulid}', [BrandController::class, 'update'])->name('edit');
        Route::post('delete/{brand:ulid}', [BrandController::class, 'delete'])->name('delete');
    });
});

Route::prefix('unit')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.unit.')->group(function () {
        Route::get('read', [UnitController::class, 'readAny'])->name('read_any');
        Route::get('read/types', [UnitController::class, 'getTypes'])->name('read_types');
        Route::get('read/{unit:ulid}', [UnitController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.unit.')->group(function () {
        Route::post('save', [UnitController::class, 'store'])->name('save');
        Route::post('edit/{unit:ulid}', [UnitController::class, 'update'])->name('edit');
        Route::post('delete/{unit:ulid}', [UnitController::class, 'delete'])->name('delete');
    });
});

Route::prefix('vat_profile')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.vat_profile.')->group(function () {
        Route::get('read', [VatProfileController::class, 'readAny'])->name('read_any');
        Route::get('read/{vat_profile:ulid}', [VatProfileController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.vat_profile.')->group(function () {
        Route::post('save', [VatProfileController::class, 'store'])->name('save');
        Route::post('edit/{vat_profile:ulid}', [VatProfileController::class, 'update'])->name('edit');
        Route::post('delete/{vat_profile:ulid}', [VatProfileController::class, 'delete'])->name('delete');
    });
});

Route::prefix('product')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.product.')->group(function () {
        Route::get('read', [ProductController::class, 'readAny'])->name('read_any');
        Route::get('read/{product:ulid}', [ProductController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.product.')->group(function () {
        Route::post('save/physical', [ProductController::class, 'storePhysical'])->name('save.physical');
        Route::post('save/service', [ProductController::class, 'storeService'])->name('save.service');
        Route::post('edit/physical/{product:ulid}', [ProductController::class, 'updatePhysical'])->name('edit.physical');
        Route::post('edit/service/{product:ulid}', [ProductController::class, 'updateService'])->name('edit.service');
        Route::post('delete/{product:ulid}', [ProductController::class, 'delete'])->name('delete');
        Route::post('image/upload', [ProductImageController::class, 'upload'])->name('image.upload');
    });
});

Route::prefix('product_unit')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.product_unit.')->group(function () {
        Route::get('read', [ProductUnitController::class, 'readAny'])->name('read_any');
        Route::get('read/{product_unit:ulid}', [ProductUnitController::class, 'read'])->name('read');
    });
});

Route::prefix('supplier')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.supplier.')->group(function () {
        Route::get('read', [SupplierController::class, 'readAny'])->name('read_any');
        Route::get('read/{supplier:ulid}', [SupplierController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.supplier.')->group(function () {
        Route::post('save', [SupplierController::class, 'store'])->name('save');
        Route::post('edit/{supplier:ulid}', [SupplierController::class, 'update'])->name('edit');
        Route::post('delete/{supplier:ulid}', [SupplierController::class, 'delete'])->name('delete');
    });
});

Route::prefix('customer_group')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.customer_group.')->group(function () {
        Route::get('read', [CustomerGroupController::class, 'readAny'])->name('read_any');
        Route::get('read/{customer_group:ulid}', [CustomerGroupController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.customer_group.')->group(function () {
        Route::post('save', [CustomerGroupController::class, 'store'])->name('save');
        Route::post('edit/{customer_group:ulid}', [CustomerGroupController::class, 'update'])->name('edit');
        Route::post('delete/{customer_group:ulid}', [CustomerGroupController::class, 'delete'])->name('delete');
    });
});

Route::prefix('customer')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.customer.')->group(function () {
        Route::get('read', [CustomerController::class, 'readAny'])->name('read_any');
        Route::get('read/{customer:ulid}', [CustomerController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.customer.')->group(function () {
        Route::post('save', [CustomerController::class, 'store'])->name('save');
        Route::post('edit/{customer:ulid}', [CustomerController::class, 'update'])->name('edit');
        Route::post('delete/{customer:ulid}', [CustomerController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_adjustment_category')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_adjustment_category.')->group(function () {
        Route::get('read', [StockAdjustmentCategoryController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_adjustment_category:ulid}', [StockAdjustmentCategoryController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_adjustment_category.')->group(function () {
        Route::post('save', [StockAdjustmentCategoryController::class, 'store'])->name('save');
        Route::post('edit/{stock_adjustment_category:ulid}', [StockAdjustmentCategoryController::class, 'update'])->name('edit');
        Route::post('delete/{stock_adjustment_category:ulid}', [StockAdjustmentCategoryController::class, 'delete'])->name('delete');
    });
});

Route::prefix('expense_category')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.expense_category.')->group(function () {
        Route::get('read', [ExpenseCategoryController::class, 'readAny'])->name('read_any');
        Route::get('read/{expense_category:ulid}', [ExpenseCategoryController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.expense_category.')->group(function () {
        Route::post('save', [ExpenseCategoryController::class, 'store'])->name('save');
        Route::post('edit/{expense_category:ulid}', [ExpenseCategoryController::class, 'update'])->name('edit');
        Route::post('delete/{expense_category:ulid}', [ExpenseCategoryController::class, 'delete'])->name('delete');
    });
});

Route::prefix('purchase_additional_cost_category')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_additional_cost_category.')->group(function () {
        Route::get('read', [PurchaseAdditionalCostCategoryController::class, 'readAny'])->name('read_any');
        Route::get('read/{pacc:ulid}', [PurchaseAdditionalCostCategoryController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.purchase_additional_cost_category.')->group(function () {
        Route::post('save', [PurchaseAdditionalCostCategoryController::class, 'store'])->name('save');
        Route::post('edit/{pacc:ulid}', [PurchaseAdditionalCostCategoryController::class, 'update'])->name('edit');
        Route::post('delete/{pacc:ulid}', [PurchaseAdditionalCostCategoryController::class, 'delete'])->name('delete');
    });
});

Route::prefix('capital_opening')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.capital_opening.')->group(function () {
        Route::get('read', [CapitalOpeningController::class, 'readAny'])->name('read_any');
        Route::get('read/{capital_opening:ulid}', [CapitalOpeningController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.capital_opening.')->group(function () {
        Route::post('save', [CapitalOpeningController::class, 'store'])->name('save');
        Route::post('edit/{capital_opening:ulid}', [CapitalOpeningController::class, 'update'])->name('edit');
        Route::post('delete/{capital_opening:ulid}', [CapitalOpeningController::class, 'delete'])->name('delete');
    });
});

Route::prefix('capital_transaction')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.capital_transaction.')->group(function () {
        Route::get('read', [CapitalTransactionController::class, 'readAny'])->name('read_any');
        Route::get('read/types', [CapitalTransactionController::class, 'getTypes'])->name('read_types');
        Route::get('read/{capital_transaction:ulid}', [CapitalTransactionController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.capital_transaction.')->group(function () {
        Route::post('save', [CapitalTransactionController::class, 'store'])->name('save');
        Route::post('edit/{capital_transaction:ulid}', [CapitalTransactionController::class, 'update'])->name('edit');
        Route::post('delete/{capital_transaction:ulid}', [CapitalTransactionController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_adjustment')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_adjustment.')->group(function () {
        Route::get('read', [StockAdjustmentController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_adjustment:ulid}', [StockAdjustmentController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_adjustment.')->group(function () {
        Route::post('save', [StockAdjustmentController::class, 'store'])->name('save');
        Route::post('edit/{stock_adjustment:ulid}', [StockAdjustmentController::class, 'update'])->name('edit');
        Route::post('delete/{stock_adjustment:ulid}', [StockAdjustmentController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_adjustment_in_item')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_adjustment_in_item.')->group(function () {
        Route::get('read', [StockAdjustmentInItemController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_adjustment_in_item:ulid}', [StockAdjustmentInItemController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_adjustment_in_item.')->group(function () {
        Route::post('save', [StockAdjustmentInItemController::class, 'store'])->name('save');
        Route::post('edit/{stock_adjustment_in_item:ulid}', [StockAdjustmentInItemController::class, 'update'])->name('edit');
        Route::post('delete/{stock_adjustment_in_item:ulid}', [StockAdjustmentInItemController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_adjustment_in_item_serial')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_adjustment_in_item_serial.')->group(function () {
        Route::get('read', [StockAdjustmentInItemSerialController::class, 'readAny'])->name('read_any');
        Route::get('read/{saii_serial:ulid}', [StockAdjustmentInItemSerialController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_adjustment_in_item_serial.')->group(function () {
        Route::post('save', [StockAdjustmentInItemSerialController::class, 'store'])->name('save');
        Route::post('edit/{saii_serial:ulid}', [StockAdjustmentInItemSerialController::class, 'update'])->name('edit');
        Route::post('delete/{saii_serial:ulid}', [StockAdjustmentInItemSerialController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_adjustment_out_item')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_adjustment_out_item.')->group(function () {
        Route::get('read', [StockAdjustmentOutItemController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_adjustment_out_item:ulid}', [StockAdjustmentOutItemController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_adjustment_out_item.')->group(function () {
        Route::post('save', [StockAdjustmentOutItemController::class, 'store'])->name('save');
        Route::post('edit/{stock_adjustment_out_item:ulid}', [StockAdjustmentOutItemController::class, 'update'])->name('edit');
        Route::post('delete/{stock_adjustment_out_item:ulid}', [StockAdjustmentOutItemController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_adjustment_out_item_serial')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_adjustment_out_item_serial.')->group(function () {
        Route::get('read', [StockAdjustmentOutItemSerialController::class, 'readAny'])->name('read_any');
        Route::get('read/{saoi_serial:ulid}', [StockAdjustmentOutItemSerialController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_adjustment_out_item_serial.')->group(function () {
        Route::post('save', [StockAdjustmentOutItemSerialController::class, 'store'])->name('save');
        Route::post('edit/{saoi_serial:ulid}', [StockAdjustmentOutItemSerialController::class, 'update'])->name('edit');
        Route::post('delete/{saoi_serial:ulid}', [StockAdjustmentOutItemSerialController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_transfer')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_transfer.')->group(function () {
        Route::get('read', [StockTransferController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_transfer:ulid}', [StockTransferController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_transfer.')->group(function () {
        Route::post('save', [StockTransferController::class, 'store'])->name('save');
        Route::post('edit/{stock_transfer:ulid}', [StockTransferController::class, 'update'])->name('edit');
        Route::post('delete/{stock_transfer:ulid}', [StockTransferController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_transfer_item')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_transfer_item.')->group(function () {
        Route::get('read', [StockTransferItemController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_transfer_item:ulid}', [StockTransferItemController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_transfer_item.')->group(function () {
        Route::post('save', [StockTransferItemController::class, 'store'])->name('save');
        Route::post('edit/{stock_transfer_item:ulid}', [StockTransferItemController::class, 'update'])->name('edit');
        Route::post('delete/{stock_transfer_item:ulid}', [StockTransferItemController::class, 'delete'])->name('delete');
    });
});

Route::prefix('stock_transfer_item_serial')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.stock_transfer_item_serial.')->group(function () {
        Route::get('read', [StockTransferItemSerialController::class, 'readAny'])->name('read_any');
        Route::get('read/{stock_transfer_item_serial:ulid}', [StockTransferItemSerialController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.stock_transfer_item_serial.')->group(function () {
        Route::post('save', [StockTransferItemSerialController::class, 'store'])->name('save');
        Route::post('edit/{stock_transfer_item_serial:ulid}', [StockTransferItemSerialController::class, 'update'])->name('edit');
        Route::post('delete/{stock_transfer_item_serial:ulid}', [StockTransferItemSerialController::class, 'delete'])->name('delete');
    });
});

Route::prefix('purchase_order')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_order.')->group(function () {
        Route::get('read', [PurchaseOrderController::class, 'readAny'])->name('read_any');
        Route::get('read/{purchase_order:ulid}', [PurchaseOrderController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.purchase_order.')->group(function () {
        Route::post('save', [PurchaseOrderController::class, 'store'])->name('save');
        Route::post('edit/{purchase_order:ulid}', [PurchaseOrderController::class, 'update'])->name('edit');
        Route::post('delete/{purchase_order:ulid}', [PurchaseOrderController::class, 'delete'])->name('delete');
    });
});

Route::prefix('purchase_order_item')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_order_item.')->group(function () {
        Route::get('read', [PurchaseOrderItemController::class, 'readAny'])->name('read_any');
        Route::get('read/{purchase_order_item:ulid}', [PurchaseOrderItemController::class, 'read'])->name('read');
    });
});

Route::prefix('purchase_order_down_payment')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_order_down_payment.')->group(function () {
        Route::get('read/allocation-statuses', [PurchaseOrderDownPaymentController::class, 'getAllocationStatuses'])->name('read_allocation_statuses');
        Route::get('read', [PurchaseOrderDownPaymentController::class, 'readAny'])->name('read_any');
        Route::get('read/{purchase_order_down_payment:ulid}', [PurchaseOrderDownPaymentController::class, 'read'])->name('read');
    });
});

Route::prefix('purchase_order_down_payment_refund')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_order_down_payment_refund.')->group(function () {
        Route::get('read', [PurchaseOrderDownPaymentRefundController::class, 'readAny'])->name('read_any');
        Route::get('read/{podp_refund:ulid}', [PurchaseOrderDownPaymentRefundController::class, 'read'])->name('read');
    });
});

Route::prefix('purchase')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase.')->group(function () {
        Route::get('read', [PurchaseController::class, 'readAny'])->name('read_any');
        Route::get('read/{purchase:ulid}', [PurchaseController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.purchase.')->group(function () {
        Route::post('save/direct', [PurchaseController::class, 'storeDirect'])->name('save.direct');
        Route::post('save/manual', [PurchaseController::class, 'storeManual'])->name('save.manual');
        Route::post('edit/direct/{purchase:ulid}', [PurchaseController::class, 'updateDirect'])->name('edit.direct');
        Route::post('edit/manual/{purchase:ulid}', [PurchaseController::class, 'updateManual'])->name('edit.manual');
        Route::post('delete/{purchase:ulid}', [PurchaseController::class, 'delete'])->name('delete');
    });
});

Route::prefix('purchase_additional_cost')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_additional_cost.')->group(function () {
        Route::get('read', [PurchaseAdditionalCostController::class, 'readAny'])->name('read_any');
        Route::get('read/{purchase_additional_cost:ulid}', [PurchaseAdditionalCostController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.purchase_additional_cost.')->group(function () {
        Route::post('save', [PurchaseAdditionalCostController::class, 'store'])->name('save');
        Route::post('edit/{purchase_additional_cost:ulid}', [PurchaseAdditionalCostController::class, 'update'])->name('edit');
        Route::post('delete/{purchase_additional_cost:ulid}', [PurchaseAdditionalCostController::class, 'delete'])->name('delete');
    });
});

Route::prefix('purchase_additional_cost_payment')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.purchase_additional_cost_payment.')->group(function () {
        Route::get('read', [PurchaseAdditionalCostPaymentController::class, 'readAny'])->name('read_any');
        Route::get('read/{purchase_additional_cost_payment:ulid}', [PurchaseAdditionalCostPaymentController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.purchase_additional_cost_payment.')->group(function () {
        Route::post('save', [PurchaseAdditionalCostPaymentController::class, 'store'])->name('save');
        Route::post('edit/{purchase_additional_cost_payment:ulid}', [PurchaseAdditionalCostPaymentController::class, 'update'])->name('edit');
        Route::post('delete/{purchase_additional_cost_payment:ulid}', [PurchaseAdditionalCostPaymentController::class, 'delete'])->name('delete');
    });
});

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1'], 'as' => 'api.get'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
        /* #region Extensions */

        /* #endregion */

        Route::group(['prefix' => 'admin', 'as' => '.admin'], function () {
            Route::group(['prefix' => 'user', 'as' => '.user'], function () {
                Route::get('read', [UserController::class, 'readAny'])->name('.read_any');
                Route::get('read/{user:ulid}', [UserController::class, 'read'])->name('.read');

                Route::get('read/{user:ulid}/tokens/count', [UserController::class, 'getTokensCount'])->name('.read.tokens.count');
            });

            Route::group(['prefix' => 'role', 'as' => '.role'], function () {
                Route::get('read', [RoleController::class, 'readAny'])->name('.read_any');
            });
        });

        Route::group(['prefix' => 'core', 'as' => '.core'], function () {
            Route::get('user/menu', [DashboardController::class, 'userMenu'])->name('.user.menu');
            Route::get('user/api', [DashboardController::class, 'userApi'])->name('.user.api');

            Route::get('search', [SearchController::class, 'search'])->name('.search');
        });

        Route::group(['prefix' => 'common', 'as' => '.common'], function () {
            Route::group(['prefix' => 'ddl', 'as' => '.ddl'], function () {
                Route::get('list/countries', [CommonController::class, 'getCountries'])->name('.list.countries');
                Route::get('list/statuses', [CommonController::class, 'getStatus'])->name('.list.statuses');
                Route::get('list/payment_term_types', [CommonController::class, 'getPaymentTermTypes'])->name('.list.payment_term_types');
                Route::get('list/rounding_types', [CommonController::class, 'getRoundingTypes'])->name('.list.rounding_types');
                Route::get('list/record_statuses', [CommonController::class, 'getRecordStatuses'])->name('.list.record_statuses');

            });
        });

        Route::group(['prefix' => 'module', 'as' => '.module'], function () {
            Route::group(['prefix' => 'profile', 'middleware' => 'validate.user', 'as' => '.profile'], function () {
                Route::get('read', [ProfileController::class, 'readProfile'])->name('.read');
            });
        });
    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth:sanctum', 'throttle:50,1'], 'as' => 'api.post'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
        /* #region Extensions */

        /* #endregion */

        Route::group(['prefix' => 'admin', 'as' => '.admin'], function () {
            Route::group(['prefix' => 'user', 'middleware' => ['precognitive'], 'as' => '.user'], function () {
                Route::post('save', [UserController::class, 'store'])->name('.save');
                Route::post('edit/{user:ulid}', [UserController::class, 'update'])->name('.edit');
            });
        });

        Route::group(['prefix' => 'core', 'middleware' => ['precognitive'], 'as' => '.core'], function () {
            Route::post('user/upload', [DashboardController::class, 'userUpload'])->name('.user.upload');
        });

        Route::group(['prefix' => 'module', 'as' => '.module'], function () {
            Route::group(['prefix' => 'profile', 'middleware' => ['precognitive'], 'as' => '.profile'], function () {
                Route::post('update/user_profile', [ProfileController::class, 'updateUserProfile'])->name('.update.user_profile');
                Route::post('update/personal_info', [ProfileController::class, 'updatePersonalInformation'])->name('.update.personal_info');
                Route::post('update/account_settings', [ProfileController::class, 'updateAccountSettings'])->name('.update.account_settings');
                Route::post('update/roles', [ProfileController::class, 'updateUserRoles'])->name('.update.roles');
                Route::post('update/password', [ProfileController::class, 'updatePassword'])->name('.update.password');
                Route::post('update/tokens', [ProfileController::class, 'updateTokens'])->name('.update.tokens');

                Route::post('send/verification', [ProfileController::class, 'sendEmailVerification'])->name('.send.email_verification');
            });
        });
    });
});
