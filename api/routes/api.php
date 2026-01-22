<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CapitalAdditionController;
use App\Http\Controllers\CapitalWithdrawalController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\NonCapitalAdditionCategoryController;
use App\Http\Controllers\NonCapitalAdditionController;
use App\Http\Controllers\NonCapitalWithdrawalCategoryController;
use App\Http\Controllers\NonCapitalWithdrawalController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseAdditionalCostCategoryController;
use App\Http\Controllers\PurchaseAdditionalCostController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderDownPaymentApplyController;
use App\Http\Controllers\PurchaseOrderDownPaymentController;
use App\Http\Controllers\PurchaseOrderProductUnitController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\PurchaseProductUnitController;
use App\Http\Controllers\PurchaseProductUnitSerialController;
use App\Http\Controllers\PurchaseReceiptController;
use App\Http\Controllers\PurchaseReturnAdditionalCostCategoryController;
use App\Http\Controllers\PurchaseReturnAdditionalCostController;
use App\Http\Controllers\PurchaseReturnProductUnitController;
use App\Http\Controllers\PurchaseReturnProductUnitSerialController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleOrderDownPaymentApplyController;
use App\Http\Controllers\SaleOrderDownPaymentController;
use App\Http\Controllers\SaleOrderProductUnitController;
use App\Http\Controllers\SalePaymentController;
use App\Http\Controllers\SaleProductUnitController;
use App\Http\Controllers\SaleProductUnitSerialController;
use App\Http\Controllers\SaleReceiptController;
use App\Http\Controllers\SaleReceiptProductUnitController;
use App\Http\Controllers\SaleReceiptProductUnitSerialController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StockAdjustmentCategoryController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockTransferProductUnitController;
use App\Http\Controllers\StockTransferProductUnitSerialController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
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

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1'], 'as' => 'api.get'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
        /* #region Extensions */
        Route::group(['prefix' => 'company', 'as' => '.company'], function () {
            Route::group(['prefix' => 'employee', 'as' => '.employee'], function () {
                Route::get('read', [EmployeeController::class, 'readAny'])->name('.read_any');
                Route::get('read/{employee:ulid}', [EmployeeController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'capital', 'as' => '.capital'], function () {
            Route::group(['prefix' => 'capital_addition', 'as' => '.capital_addition'], function () {
                Route::get('read', [CapitalAdditionController::class, 'readAny'])->name('.read_any');
                Route::get('read/{capital_addition:ulid}', [CapitalAdditionController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'capital_withdrawal', 'as' => '.capital_withdrawal'], function () {
                Route::get('read', [CapitalWithdrawalController::class, 'readAny'])->name('.read_any');
                Route::get('read/{capital_withdrawal:ulid}', [CapitalWithdrawalController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'non_capital_addition_category', 'as' => '.non_capital_addition_category'], function () {
                Route::get('read', [NonCapitalAdditionCategoryController::class, 'readAny'])->name('.read_any');
                Route::get('read/{non_capital_addition_category:ulid}', [NonCapitalAdditionCategoryController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'non_capital_addition', 'as' => '.non_capital_addition'], function () {
                Route::get('read', [NonCapitalAdditionController::class, 'readAny'])->name('.read_any');
                Route::get('read/{non_capital_addition:ulid}', [NonCapitalAdditionController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'non_capital_withdrawal_category', 'as' => '.non_capital_withdrawal_category'], function () {
                Route::get('read', [NonCapitalWithdrawalCategoryController::class, 'readAny'])->name('.read_any');
                Route::get('read/{non _capital_withdrawal_category:ulid}', [NonCapitalWithdrawalCategoryController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'non_capital_withdrawal', 'as' => '.non_capital_withdrawal'], function () {
                Route::get('read', [NonCapitalWithdrawalController::class, 'readAny'])->name('.read_any');
                Route::get('read/{non _capital_withdrawal:ulid}', [NonCapitalWithdrawalController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'customer', 'as' => '.customer'], function () {
            Route::group(['prefix' => 'customer', 'as' => '.customer'], function () {
                Route::get('read', [CustomerController::class, 'readAny'])->name('.read_any');
                Route::get('read/{customer:ulid}', [CustomerController::class, 'read'])->name('.read');
            });

            Route::group(['prefix' => 'customer_address', 'as' => '.customer_address'], function () {
                Route::get('read', [CustomerAddressController::class, 'readAny'])->name('.read_any');
                Route::get('read/{customer_address:ulid}', [CustomerAddressController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'purchase_order', 'as' => '.purchase_order'], function () {
            Route::group(['prefix' => 'purchase_order', 'as' => '.purchase_order'], function () {
                Route::get('read', [PurchaseOrderController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_order:ulid}', [PurchaseOrderController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_order_product_unit', 'as' => '.purchase_order_product_unit'], function () {
                Route::get('read', [PurchaseOrderProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_order_product_unit:ulid}', [PurchaseOrderProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_order_down_payment', 'as' => '.purchase_order_down_payment'], function () {
                Route::get('read', [PurchaseOrderDownPaymentController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_order_down_payment:ulid}', [PurchaseOrderDownPaymentController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'po_down_payment_apply', 'as' => '.po_down_payment_apply'], function () {
                Route::get('read', [PurchaseOrderDownPaymentApplyController::class, 'readAny'])->name('.read_any');
                Route::get('read/{po_down_payment_apply:ulid}', [PurchaseOrderDownPaymentApplyController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'purchase', 'as' => '.purchase'], function () {
            Route::group(['prefix' => 'purchase', 'as' => '.purchase'], function () {
                Route::get('read', [PurchaseController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase:ulid}', [PurchaseController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_product_unit', 'as' => '.purchase_product_unit'], function () {
                Route::get('read', [PurchaseProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_product_unit:ulid}', [PurchaseProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_product_unit_serial', 'as' => '.purchase_product_unit_serial'], function () {
                Route::get('read', [PurchaseProductUnitSerialController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_product_unit_serial:ulid}', [PurchaseProductUnitSerialController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_additional_category', 'as' => '.purchase_additional_category'], function () {
                Route::get('read', [PurchaseAdditionalCostCategoryController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_additional_category:ulid}', [PurchaseAdditionalCostCategoryController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_additional_cost', 'as' => '.purchase_additional_cost'], function () {
                Route::get('read', [PurchaseAdditionalCostController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_additional_cost:ulid}', [PurchaseAdditionalCostController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_return_product_unit', 'as' => '.purchase_return_product_unit'], function () {
                Route::get('read', [PurchaseReturnProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_return_product_unit:ulid}', [PurchaseReturnProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_return_unit_serial', 'as' => '.purchase_return_unit_serial'], function () {
                Route::get('read', [PurchaseReturnProductUnitSerialController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_return_unit_serial:ulid}', [PurchaseReturnProductUnitSerialController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_additional_cost', 'as' => '.purchase_additional_cost'], function () {
                Route::get('read', [PurchaseReturnAdditionalCostCategoryController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_additional_cost:ulid}', [PurchaseReturnAdditionalCostCategoryController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_return_additional_cost', 'as' => '.purchase_return_additional_cost'], function () {
                Route::get('read', [PurchaseReturnAdditionalCostController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_return_additional_cost:ulid}', [PurchaseReturnAdditionalCostController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_payment', 'as' => '.purchase_payment'], function () {
                Route::get('read', [PurchasePaymentController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_payment:ulid}', [PurchasePaymentController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_receipt', 'as' => '.purchase_receipt'], function () {
                Route::get('read', [PurchaseReceiptController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_receipt:ulid}', [PurchaseReceiptController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'purchase_receipt_product_unit', 'as' => '.purchase_receipt_product_unit'], function () {
                Route::get('read', [PurchaseReturnProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{purchase_receipt_product_unit:ulid}', [PurchaseReturnProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'receipt_product_unit_serial', 'as' => '.receipt_product_unit_serial'], function () {
                Route::get('read', [PurchaseReturnProductUnitSerialController::class, 'readAny'])->name('.read_any');
                Route::get('read/{receipt_product_unit_serial:ulid}', [PurchaseReturnProductUnitSerialController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'stock_transfer', 'as' => '.stock_transfer'], function () {
            Route::group(['prefix' => 'stock_transfer', 'as' => '.stock_transfer'], function () {
                Route::get('read', [StockTransferController::class, 'readAny'])->name('.read_any');
                Route::get('read/{stock_transfer:ulid}', [StockTransferController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'stock_transfer_product_unit', 'as' => '.stock_transfer_product_unit'], function () {
                Route::get('read', [StockTransferProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{stock_transfer_product_unit:ulid}', [StockTransferProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'stock_transfer_product_unit_serial', 'as' => '.stock_transfer_product_unit_serial'], function () {
                Route::get('read', [StockTransferProductUnitSerialController::class, 'readAny'])->name('.read_any');
                Route::get('read/{stock_trf_product_unit_serial:ulid}', [StockTransferProductUnitSerialController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'sales', 'as' => '.sales'], function () {
            Route::group(['prefix' => 'sales_order', 'as' => '.sales_order'], function () {
                Route::get('read', [SalesOrderController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sales_order:ulid}', [SalesOrderController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_order_product_unit', 'as' => '.sale_order_product_unit'], function () {
                Route::get('read', [SaleOrderProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_order_product_unit:ulid}', [SaleOrderProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_order_down_payment', 'as' => '.sale_order_down_payment'], function () {
                Route::get('read', [SaleOrderDownPaymentController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_order_down_payment:ulid}', [SaleOrderDownPaymentController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_order_down_payment_apply', 'as' => '.sale_order_down_payment_apply'], function () {
                Route::get('read', [SaleOrderDownPaymentApplyController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_order_down_payment_apply:ulid}', [SaleOrderDownPaymentApplyController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale', 'as' => '.sale'], function () {
                Route::get('read', [SaleController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale:ulid}', [SaleController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_product_unit', 'as' => '.sale_product_unit'], function () {
                Route::get('read', [SaleProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_product_unit:ulid}', [SaleProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_product_unit_serial', 'as' => '.sale_product_unit_serial'], function () {
                Route::get('read', [SaleProductUnitSerialController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_product_unit_serial:ulid}', [SaleProductUnitSerialController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_payment', 'as' => '.sale_payment'], function () {
                Route::get('read', [SalePaymentController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_payment:ulid}', [SalePaymentController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_receipt', 'as' => '.sale_receipt'], function () {
                Route::get('read', [SaleReceiptController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_receipt:ulid}', [SaleReceiptController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_receipt_product_unit', 'as' => '.sale_receipt_product_unit'], function () {
                Route::get('read', [SaleReceiptProductUnitController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_receipt_product_unit:ulid}', [SaleReceiptProductUnitController::class, 'read'])->name('.read');
            });
            Route::group(['prefix' => 'sale_receipt_product_unit_serial', 'as' => '.sale_receipt_product_unit_serial'], function () {
                Route::get('read', [SaleReceiptProductUnitSerialController::class, 'readAny'])->name('.read_any');
                Route::get('read/{sale_receipt_product_unit_serial:ulid}', [SaleReceiptProductUnitSerialController::class, 'read'])->name('.read');
            });
        });

        Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function () {
            Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function () {
                Route::get('read', [SupplierController::class, 'readAny'])->name('.read_any');
                Route::get('read/{supplier:ulid}', [SupplierController::class, 'read'])->name('.read');
            });
        });
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
        Route::group(['prefix' => 'company', 'middleware' => ['precognitive'], 'as' => '.company'], function () {
            Route::group(['prefix' => 'employee', 'as' => '.employee'], function () {
                Route::post('save', [EmployeeController::class, 'store'])->name('.save');
                Route::post('edit/{employee:ulid}', [EmployeeController::class, 'update'])->name('.edit');
                Route::post('delete/{employee:ulid}', [EmployeeController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'capital', 'middleware' => ['precognitive'], 'as' => '.capital'], function () {
            Route::group(['prefix' => 'capital_addition', 'as' => '.capital_addition'], function () {
                Route::post('save', [CapitalAdditionController::class, 'store'])->name('.save');
                Route::post('edit/{capital_addition:ulid}', [CapitalAdditionController::class, 'update'])->name('.edit');
                Route::post('delete/{capital_addition:ulid}', [CapitalAdditionController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'capital_withdrawal', 'as' => '.capital_withdrawal'], function () {
                Route::post('save', [CapitalWithdrawalController::class, 'store'])->name('.save');
                Route::post('edit/{capital_withdrawal:ulid}', [CapitalWithdrawalController::class, 'update'])->name('.edit');
                Route::post('delete/{capital_withdrawal:ulid}', [CapitalWithdrawalController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'non_capital_addition_category', 'as' => '.non_capital_addition_category'], function () {
                Route::post('save', [NonCapitalAdditionCategoryController::class, 'store'])->name('.save');
                Route::post('edit/{non_capital_addition_category:ulid}', [NonCapitalAdditionCategoryController::class, 'update'])->name('.edit');
                Route::post('delete/{non_capital_addition_category:ulid}', [NonCapitalAdditionCategoryController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'non_capital_addition', 'as' => '.non_capital_addition'], function () {
                Route::post('save', [NonCapitalAdditionController::class, 'store'])->name('.save');
                Route::post('edit/{non_capital_addition:ulid}', [NonCapitalAdditionController::class, 'update'])->name('.edit');
                Route::post('delete/{non_capital_addition:ulid}', [NonCapitalAdditionController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'non_capital_withdrawal_category', 'as' => '.non_capital_withdrawal_category'], function () {
                Route::post('save', [NonCapitalWithdrawalCategoryController::class, 'store'])->name('.save');
                Route::post('edit/{non_capital_withdrawal_category:ulid}', [NonCapitalWithdrawalCategoryController::class, 'update'])->name('.edit');
                Route::post('delete/{non_capital_withdrawal_category:ulid}', [NonCapitalWithdrawalCategoryController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'non_capital_withdrawal', 'as' => '.non_capital_withdrawal'], function () {
                Route::post('save', [NonCapitalWithdrawalController::class, 'store'])->name('.save');
                Route::post('edit/{non_capital_withdrawal:ulid}', [NonCapitalWithdrawalController::class, 'update'])->name('.edit');
                Route::post('delete/{non_capital_withdrawal:ulid}', [NonCapitalWithdrawalController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'customer', 'middleware' => ['precognitive'], 'as' => '.customer'], function () {
            Route::group(['prefix' => 'customer', 'as' => '.customer'], function () {
                Route::post('save', [CustomerController::class, 'store'])->name('.save');
                Route::post('edit/{customer:ulid}', [CustomerController::class, 'update'])->name('.edit');
                Route::post('delete/{customer:ulid}', [CustomerController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'customer_address', 'as' => '.customer_address'], function () {
                Route::post('save', [CustomerAddressController::class, 'store'])->name('.save');
                Route::post('edit/{customer_address:ulid}', [CustomerAddressController::class, 'update'])->name('.edit');
                Route::post('delete/{customer_address:ulid}', [CustomerAddressController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'purcase_order', 'middleware' => ['precognitive'], 'as' => '.purcase_order'], function () {
            Route::group(['prefix' => 'purcase_order', 'as' => '.purcase_order'], function () {
                Route::post('save', [PurchaseOrderController::class, 'store'])->name('.save');
                Route::post('edit/{purcase_order:ulid}', [PurchaseOrderController::class, 'update'])->name('.edit');
                Route::post('delete/{purcase_order:ulid}', [PurchaseOrderController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purcase_order_product_unit', 'as' => '.purcase_order_product_unit'], function () {
                Route::post('save', [PurchaseOrderProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{purcase_order_product_unit:ulid}', [PurchaseOrderProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{purcase_order_product_unit:ulid}', [PurchaseOrderProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purcase_order_down_payment', 'as' => '.purcase_order_down_payment'], function () {
                Route::post('save', [PurchaseOrderDownPaymentController::class, 'store'])->name('.save');
                Route::post('edit/{purcase_order_down_payment:ulid}', [PurchaseOrderDownPaymentController::class, 'update'])->name('.edit');
                Route::post('delete/{purcase_order_down_payment:ulid}', [PurchaseOrderDownPaymentController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purcase_order_down_payment_apply', 'as' => '.purcase_order_down_payment_apply'], function () {
                Route::post('save', [PurchaseOrderDownPaymentApplyController::class, 'store'])->name('.save');
                Route::post('edit/{purcase_order_down_payment_apply:ulid}', [PurchaseOrderDownPaymentApplyController::class, 'update'])->name('.edit');
                Route::post('delete/{purcase_order_down_payment_apply:ulid}', [PurchaseOrderDownPaymentApplyController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'purchase', 'middleware' => ['precognitive'], 'as' => '.purchase'], function () {
            Route::group(['prefix' => 'purchase', 'as' => '.purchase'], function () {
                Route::post('save', [PurchaseController::class, 'store'])->name('.save');
                Route::post('edit/{purchase:ulid}', [PurchaseController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase:ulid}', [PurchaseController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_product_unit', 'as' => '.purchase_product_unit'], function () {
                Route::post('save', [PurchaseProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_product_unit:ulid}', [PurchaseProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_product_unit:ulid}', [PurchaseProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_product_unit_serial', 'as' => '.purchase_product_unit_serial'], function () {
                Route::post('save', [PurchaseProductUnitSerialController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_product_unit_serial:ulid}', [PurchaseProductUnitSerialController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_product_unit_serial:ulid}', [PurchaseProductUnitSerialController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_additional_category', 'as' => '.purchase_additional_category'], function () {
                Route::post('save', [PurchaseAdditionalCostCategoryController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_additional_category:ulid}', [PurchaseAdditionalCostCategoryController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_additional_category:ulid}', [PurchaseAdditionalCostCategoryController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_additional_cost', 'as' => '.purchase_additional_cost'], function () {
                Route::post('save', [PurchaseAdditionalCostController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_additional_cost:ulid}', [PurchaseAdditionalCostController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_additional_cost:ulid}', [PurchaseAdditionalCostController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_return_product_unit', 'as' => '.purchase_return_product_unit'], function () {
                Route::post('save', [PurchaseReturnProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_return_product_unit:ulid}', [PurchaseReturnProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_return_product_unit:ulid}', [PurchaseReturnProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_return_unit_serial', 'as' => '.purchase_return_unit_serial'], function () {
                Route::post('save', [PurchaseReturnProductUnitSerialController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_return_unit_serial:ulid}', [PurchaseReturnProductUnitSerialController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_return_unit_serial:ulid}', [PurchaseReturnProductUnitSerialController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_additional_cost', 'as' => '.purchase_additional_cost'], function () {
                Route::post('save', [PurchaseReturnAdditionalCostCategoryController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_additional_cost:ulid}', [PurchaseReturnAdditionalCostCategoryController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_additional_cost:ulid}', [PurchaseReturnAdditionalCostCategoryController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_return_additional_cost', 'as' => '.purchase_return_additional_cost'], function () {
                Route::post('save', [PurchaseReturnAdditionalCostController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_return_additional_cost:ulid}', [PurchaseReturnAdditionalCostController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_return_additional_cost:ulid}', [PurchaseReturnAdditionalCostController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_payment', 'as' => '.purchase_payment'], function () {
                Route::post('save', [PurchasePaymentController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_payment:ulid}', [PurchasePaymentController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_payment:ulid}', [PurchasePaymentController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_receipt', 'as' => '.purchase_receipt'], function () {
                Route::post('save', [PurchaseReceiptController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_receipt:ulid}', [PurchaseReceiptController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_receipt:ulid}', [PurchaseReceiptController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'purchase_receipt_product_unit', 'as' => '.purchase_receipt_product_unit'], function () {
                Route::post('save', [PurchaseReturnProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{purchase_receipt_product_unit:ulid}', [PurchaseReturnProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{purchase_receipt_product_unit:ulid}', [PurchaseReturnProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'receipt_product_unit_serial', 'as' => '.receipt_product_unit_serial'], function () {
                Route::post('save', [PurchaseReturnProductUnitSerialController::class, 'store'])->name('.save');
                Route::post('edit/{receipt_product_unit_serial:ulid}', [PurchaseReturnProductUnitSerialController::class, 'update'])->name('.edit');
                Route::post('delete/{receipt_product_unit_serial:ulid}', [PurchaseReturnProductUnitSerialController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'stock_transfer', 'middleware' => ['precognitive'], 'as' => '.stock_transfer'], function () {
            Route::group(['prefix' => 'stock_transfer', 'as' => '.stock_transfer'], function () {
                Route::post('save', [StockTransferController::class, 'store'])->name('.save');
                Route::post('edit/{stock_transfer:ulid}', [StockTransferController::class, 'update'])->name('.edit');
                Route::post('delete/{stock_transfer:ulid}', [StockTransferController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'stock_transfer_product_unit', 'as' => '.stock_transfer_product_unit'], function () {
                Route::post('save', [StockTransferProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{stock_transfer_product_unit:ulid}', [StockTransferProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{stock_transfer_product_unit:ulid}', [StockTransferProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'stock_transfer_product_unit_serial', 'as' => '.stock_transfer_product_unit_serial'], function () {
                Route::post('save', [StockTransferProductUnitSerialController::class, 'store'])->name('.save');
                Route::post('edit/{stock_trf_product_unit_serial:ulid}', [StockTransferProductUnitSerialController::class, 'update'])->name('.edit');
                Route::post('delete/{stock_trf_product_unit_serial:ulid}', [StockTransferProductUnitSerialController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'sales', 'middleware' => ['precognitive'], 'as' => '.sales'], function () {
            Route::group(['prefix' => 'sales_order', 'as' => '.sales_order'], function () {
                Route::post('save', [SalesOrderController::class, 'store'])->name('.save');
                Route::post('edit/{sales_order:ulid}', [SalesOrderController::class, 'update'])->name('.edit');
                Route::post('delete/{sales_order:ulid}', [SalesOrderController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_order_product_unit', 'as' => '.sale_order_product_unit'], function () {
                Route::post('save', [SaleOrderProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{sale_order_product_unit:ulid}', [SaleOrderProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_order_product_unit:ulid}', [SaleOrderProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_order_down_payment', 'as' => '.sale_order_down_payment'], function () {
                Route::post('save', [SaleOrderDownPaymentController::class, 'store'])->name('.save');
                Route::post('edit/{sale_order_down_payment:ulid}', [SaleOrderDownPaymentController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_order_down_payment:ulid}', [SaleOrderDownPaymentController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_order_down_payment_apply', 'as' => '.sale_order_down_payment_apply'], function () {
                Route::post('save', [SaleOrderDownPaymentApplyController::class, 'store'])->name('.save');
                Route::post('edit/{sale_order_down_payment_apply:ulid}', [SaleOrderDownPaymentApplyController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_order_down_payment_apply:ulid}', [SaleOrderDownPaymentApplyController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale', 'as' => '.sale'], function () {
                Route::post('save', [SaleController::class, 'store'])->name('.save');
                Route::post('edit/{sale:ulid}', [SaleController::class, 'update'])->name('.edit');
                Route::post('delete/{sale:ulid}', [SaleController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_product_unit', 'as' => '.sale_product_unit'], function () {
                Route::post('save', [SaleProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{sale_product_unit:ulid}', [SaleProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_product_unit:ulid}', [SaleProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_product_unit_serial', 'as' => '.sale_product_unit_serial'], function () {
                Route::post('save', [SaleProductUnitSerialController::class, 'store'])->name('.save');
                Route::post('edit/{sale_product_unit_serial:ulid}', [SaleProductUnitSerialController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_product_unit_serial:ulid}', [SaleProductUnitSerialController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_payment', 'as' => '.sale_payment'], function () {
                Route::post('save', [SalePaymentController::class, 'store'])->name('.save');
                Route::post('edit/{sale_payment:ulid}', [SalePaymentController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_payment:ulid}', [SalePaymentController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_receipt', 'as' => '.sale_receipt'], function () {
                Route::post('save', [SaleReceiptController::class, 'store'])->name('.save');
                Route::post('edit/{sale_receipt:ulid}', [SaleReceiptController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_receipt:ulid}', [SaleReceiptController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_receipt_product_unit', 'as' => '.sale_receipt_product_unit'], function () {
                Route::post('save', [SaleReceiptProductUnitController::class, 'store'])->name('.save');
                Route::post('edit/{sale_receipt_product_unit:ulid}', [SaleReceiptProductUnitController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_receipt_product_unit:ulid}', [SaleReceiptProductUnitController::class, 'delete'])->name('.delete');
            });
            Route::group(['prefix' => 'sale_receipt_product_unit_serial', 'as' => '.sale_receipt_product_unit_serial'], function () {
                Route::post('save', [SaleReceiptProductUnitSerialController::class, 'store'])->name('.save');
                Route::post('edit/{sale_receipt_product_unit_serial:ulid}', [SaleReceiptProductUnitSerialController::class, 'update'])->name('.edit');
                Route::post('delete/{sale_receipt_product_unit_serial:ulid}', [SaleReceiptProductUnitSerialController::class, 'delete'])->name('.delete');
            });
        });

        Route::group(['prefix' => 'supplier', 'middleware' => ['precognitive'], 'as' => '.supplier'], function () {
            Route::group(['prefix' => 'supplier', 'as' => '.supplier'], function () {
                Route::post('save', [SupplierController::class, 'store'])->name('.save');
                Route::post('edit/{supplier:ulid}', [SupplierController::class, 'update'])->name('.edit');
                Route::post('delete/{supplier:ulid}', [SupplierController::class, 'delete'])->name('.delete');
            });
        });
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
