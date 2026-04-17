<?php

namespace App\Actions\StockAdjustmentOutItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentOutItemSerialCreateDTO;
use App\DTOs\StockAdjustmentOutItemSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockAdjustmentOutItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentOutItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'stockAdjustment.category',
        'stockAdjustment.inWarehouse',
        'stockAdjustment.outWarehouse',
        'stockAdjustmentOutItem.company',
        'stockAdjustmentOutItem.branch',
        'stockAdjustmentOutItem.stockAdjustment.category',
        'stockAdjustmentOutItem.stockAdjustment.inWarehouse',
        'stockAdjustmentOutItem.stockAdjustment.outWarehouse',
        'stockAdjustmentOutItem.productUnit.unit',
        'stockAdjustmentOutItem.productUnit.product.category',
        'stockAdjustmentOutItem.productUnit.product.brand',
        'stockAdjustmentOutItem.productUnit.product.baseProductUnit.unit',
        'stockAdjustmentOutItem.productUnit.product.images',
        'stockAdjustmentOutItem.productUnit.product.mainImage',
    ];

    protected StockSerialTransactionActions $stockSerialTransactionActions;

    public function __construct(StockSerialTransactionActions $stockSerialTransactionActions)
    {
        $this->stockSerialTransactionActions = $stockSerialTransactionActions;
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
        ?string $stockAdjustmentProductUnitCode,
        ?string $stockAdjustmentProductUnitProductName,
        ?int $stockAdjustmentProductUnitProductCategoryId,
        ?int $stockAdjustmentProductUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentOutItemSerial::select('stock_adjustment_out_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'stock_adjustment_out_item_serials.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_out_item_serials.stock_adjustment_id')
            ->join('stock_adjustment_out_items', 'stock_adjustment_out_items.id', '=', 'stock_adjustment_out_item_serials.stock_adjustment_out_item_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_out_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_adjustment_out_item_serials', $companyId)
            ->whereBranchId('stock_adjustment_out_item_serials', $branchId)
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
            $stockAdjustmentProductUnitCode,
            $stockAdjustmentProductUnitProductName,
            $stockAdjustmentProductUnitProductCategoryId,
            $stockAdjustmentProductUnitProductBrandId,
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

            if ($stockAdjustmentProductUnitCode) {
                $query->where('product_units.code', 'like', '%'.$stockAdjustmentProductUnitCode.'%');
            }

            if ($stockAdjustmentProductUnitProductName) {
                $query->where('products.name', 'like', '%'.$stockAdjustmentProductUnitProductName.'%');
            }

            if ($stockAdjustmentProductUnitProductCategoryId) {
                $query->where('products.category_id', $stockAdjustmentProductUnitProductCategoryId);
            }

            if ($stockAdjustmentProductUnitProductBrandId) {
                $query->where('products.brand_id', $stockAdjustmentProductUnitProductBrandId);
            }
        });

        $query->orderBy('stock_adjustments.date', 'desc')
            ->orderBy('stock_adjustment_out_item_serials.id', 'asc');

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
                    empty($stockAdjustmentProductUnitCode) ? '[empty]' : $stockAdjustmentProductUnitCode,
                    empty($stockAdjustmentProductUnitProductName) ? '[empty]' : $stockAdjustmentProductUnitProductName,
                    $stockAdjustmentProductUnitProductCategoryId ?? '[null]',
                    $stockAdjustmentProductUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_adjustment_out_item_serial_'.implode('_', $cacheParams);

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

    public function read(StockAdjustmentOutItemSerial $stockAdjustmentOutItemSerial): StockAdjustmentOutItemSerial
    {
        return $stockAdjustmentOutItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(StockAdjustmentOutItemSerialCreateDTO $data): StockAdjustmentOutItemSerial
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutItemSerial = new StockAdjustmentOutItemSerial();
            $stockAdjustmentOutItemSerial->company_id = $data->companyId;
            $stockAdjustmentOutItemSerial->branch_id = $data->branchId;
            $stockAdjustmentOutItemSerial->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentOutItemSerial->stock_adjustment_out_item_id = $data->stockAdjustmentOutItemId;
            $stockAdjustmentOutItemSerial->serial = $data->serial;
            $stockAdjustmentOutItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockAdjustmentOutItemSerial(
                stockAdjustmentOutItemSerial: $stockAdjustmentOutItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $stockAdjustmentOutItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockAdjustmentOutItemSerial $stockAdjustmentOutItemSerial, StockAdjustmentOutItemSerialUpdateDTO $data): StockAdjustmentOutItemSerial
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutItemSerial->serial = $data->serial;
            $stockAdjustmentOutItemSerial->save();

            $stockSerialTransaction = $stockAdjustmentOutItemSerial->stockSerialTransaction;

            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromStockAdjustmentOutItemSerial($stockAdjustmentOutItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockAdjustmentOutItemSerial($stockAdjustmentOutItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $stockAdjustmentOutItemSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentOutItemSerial $stockAdjustmentOutItemSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockSerialTransaction = $stockAdjustmentOutItemSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $retval = $stockAdjustmentOutItemSerial->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
