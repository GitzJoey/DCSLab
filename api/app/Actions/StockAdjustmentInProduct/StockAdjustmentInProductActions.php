<?php

namespace App\Actions\StockAdjustmentInProduct;

use App\Actions\StockAdjustmentInProductSerial\StockAdjustmentInProductSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentInProductCreateDTO;
use App\DTOs\StockAdjustmentInProductSerialCreateDTO;
use App\DTOs\StockAdjustmentInProductSerialUpdateDTO;
use App\DTOs\StockAdjustmentInProductUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockAdjustmentInProduct;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentInProductActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockAdjustmentInProductSerialActions;

    private $stockTransactionActions;

    public function __construct(
        StockTransactionActions $stockTransactionActions,
        StockAdjustmentInProductSerialActions $stockAdjustmentInProductSerialActions,
    ) {
        $this->stockTransactionActions = $stockTransactionActions;
        $this->stockAdjustmentInProductSerialActions = $stockAdjustmentInProductSerialActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $stockAdjustmentCode,
        ?string $stockAdjustmentStartDate,
        ?string $stockAdjustmentEndDate,
        ?int $stockAdjustmentCategoryId,
        ?int $stockAdjustmentInWarehouseId,
        ?int $stockAdjustmentOutWarehouseId,
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentInProduct::select('stock_adjustment_in_products.*')
            ->with([
                'company',
                'branch',
                'stockAdjustment',
                'productUnit',
                'productUnit.unit',
                'productUnit.product.images',
            ])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_in_products.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_in_products.stock_adjustment_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_in_products.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_adjustment_in_products', $companyId)
            ->whereBranchId('stock_adjustment_in_products', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $stockAdjustmentCode,
            $stockAdjustmentStartDate,
            $stockAdjustmentEndDate,
            $stockAdjustmentCategoryId,
            $stockAdjustmentInWarehouseId,
            $stockAdjustmentOutWarehouseId,
            $productUnitCode,
            $productUnitProductName,
            $productUnitProductCategoryId,
            $productUnitProductBrandId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            $stockAdjustmentStartDateUtc = $stockAdjustmentStartDate ? TimezoneHelper::convertToUTC($stockAdjustmentStartDate) : null;
            if ($stockAdjustmentStartDateUtc) {
                $query->where('stock_adjustments.date', '>=', $stockAdjustmentStartDateUtc);
            }

            $stockAdjustmentEndDateUtc = $stockAdjustmentEndDate ? TimezoneHelper::convertToUTC($stockAdjustmentEndDate) : null;
            if ($stockAdjustmentEndDateUtc) {
                $query->where('stock_adjustments.date', '<=', $stockAdjustmentEndDateUtc);
            }

            if ($stockAdjustmentCode) {
                $query->where('stock_adjustments.code', $stockAdjustmentCode);
            }

            if ($stockAdjustmentCategoryId) {
                $query->where('stock_adjustments.category_id', $stockAdjustmentCategoryId);
            }

            if ($stockAdjustmentInWarehouseId) {
                $query->where('stock_adjustments.in_warehouse_id', $stockAdjustmentInWarehouseId);
            }

            if ($stockAdjustmentOutWarehouseId) {
                $query->where('stock_adjustments.out_warehouse_id', $stockAdjustmentOutWarehouseId);
            }

            if ($productUnitCode) {
                $query->where('product_units.code', 'like', '%'.$productUnitCode.'%');
            }

            if ($productUnitProductName) {
                $query->where('products.name', 'like', '%'.$productUnitProductName.'%');
            }

            if ($productUnitProductCategoryId) {
                $query->where('products.category_id', $productUnitProductCategoryId);
            }

            if ($productUnitProductBrandId) {
                $query->where('products.brand_id', $productUnitProductBrandId);
            }
        });

        $query->orderBy('stock_adjustments.date', 'desc')
            ->orderBy('stock_adjustment_in_products.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($stockAdjustmentCode) ? '[empty]' : $stockAdjustmentCode,
                    $stockAdjustmentStartDate ?? '[null]',
                    $stockAdjustmentEndDate ?? '[null]',
                    $stockAdjustmentCategoryId ?? '[null]',
                    $stockAdjustmentInWarehouseId ?? '[null]',
                    $stockAdjustmentOutWarehouseId ?? '[null]',
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_adjustment_in_product_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
                    $this->saveToCache($cacheKey, $result);
                }

                return $result;
            } catch (Exception $e) {
                $this->loggerDebug(__METHOD__, $e);
                throw $e;
            } finally {
                $execution_time = microtime(true) - $timer_start;
                $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
            }
        }

        return $query;
    }

    public function read(StockAdjustmentInProduct $stockAdjustmentInProduct): StockAdjustmentInProduct
    {
        return $stockAdjustmentInProduct->load([
            'company',
            'branch',
            'stockAdjustment',
            'productUnit',
            'productUnit.unit',
            'productUnit.product.images',
            'serials',
        ]);
    }

    public function create(StockAdjustmentInProductCreateDTO $data): StockAdjustmentInProduct
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentInProduct = new StockAdjustmentInProduct();
            $stockAdjustmentInProduct->company_id = $data->companyId;
            $stockAdjustmentInProduct->branch_id = $data->branchId;
            $stockAdjustmentInProduct->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentInProduct->qty = $data->qty;
            $stockAdjustmentInProduct->product_unit_id = $data->productUnitId;
            $stockAdjustmentInProduct->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentInProduct->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockAdjustmentInProduct->product_unit_cogs = $data->productUnitCogs;
            $stockAdjustmentInProduct->product_unit_total_cogs = $data->qty * $data->productUnitCogs;
            $stockAdjustmentInProduct->product_unit_base_unit_cogs = $stockAdjustmentInProduct->product_unit_total_cogs / $stockAdjustmentInProduct->product_unit_qty_base;
            $stockAdjustmentInProduct->remarks = $data->remarks;
            $stockAdjustmentInProduct->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockAdjustmentInProduct($stockAdjustmentInProduct)
            );

            foreach ($data->serials as $serial) {
                $dto = StockAdjustmentInProductSerialCreateDTO::fromStockAdjustmentInProduct($stockAdjustmentInProduct, $serial['serial']);
                $this->stockAdjustmentInProductSerialActions->create($dto);
            }

            $this->flushCache();

            return $stockAdjustmentInProduct;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockAdjustmentInProduct $stockAdjustmentInProduct, StockAdjustmentInProductUpdateDTO $data): StockAdjustmentInProduct
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentInProduct->qty = $data->qty;
            $stockAdjustmentInProduct->product_unit_id = $data->productUnitId;
            $stockAdjustmentInProduct->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentInProduct->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockAdjustmentInProduct->product_unit_cogs = $data->productUnitCogs;
            $stockAdjustmentInProduct->product_unit_total_cogs = $data->qty * $data->productUnitCogs;
            $stockAdjustmentInProduct->product_unit_base_unit_cogs = $stockAdjustmentInProduct->product_unit_total_cogs / $stockAdjustmentInProduct->product_unit_qty_base;
            $stockAdjustmentInProduct->remarks = $data->remarks;
            $stockAdjustmentInProduct->save();

            $stockTransaction = $stockAdjustmentInProduct->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromStockAdjustmentInProduct($stockAdjustmentInProduct);
                $stockTransaction = $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockAdjustmentInProduct($stockAdjustmentInProduct);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $stockAdjustmentInProductSerial = $stockAdjustmentInProduct->serials()->findOrFail($deleteId);
                $this->stockAdjustmentInProductSerialActions->delete($stockAdjustmentInProductSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $stockAdjustmentInProductSerial = $stockAdjustmentInProduct->serials()->findOrFail($serial['id']);
                    $dto = StockAdjustmentInProductSerialUpdateDTO::fromStockAdjustmentInProductSerial($stockAdjustmentInProductSerial, $serial['serial']);

                    $this->stockAdjustmentInProductSerialActions->update($stockAdjustmentInProductSerial, $dto);
                } else {
                    $dto = StockAdjustmentInProductSerialCreateDTO::fromStockAdjustmentInProduct($stockAdjustmentInProduct, $serial['serial']);
                    $this->stockAdjustmentInProductSerialActions->create($dto);
                }
            }

            $this->flushCache();

            return $stockAdjustmentInProduct;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentInProduct $stockAdjustmentInProduct): bool
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = $stockAdjustmentInProduct->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            $serials = $stockAdjustmentInProduct->serials;
            foreach ($serials as $serial) {
                $this->stockAdjustmentInProductSerialActions->delete($serial);
            }

            $result = $stockAdjustmentInProduct->delete();

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
