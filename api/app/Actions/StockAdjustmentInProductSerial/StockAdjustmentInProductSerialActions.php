<?php

namespace App\Actions\StockAdjustmentInProductSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentInProductSerialCreateDTO;
use App\DTOs\StockAdjustmentInProductSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockAdjustmentInProductSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentInProductSerialActions
{
    use CacheHelper;
    use LoggerHelper;

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
        ?string $stockAdjustmentProductName,
        ?int $stockAdjustmentProductCategoryId,
        ?int $stockAdjustmentProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentInProductSerial::select('stock_adjustment_in_product_serials.*')
            ->with([
                'company',
                'branch',
                'stockAdjustment',
                'stockAdjustmentInProduct.productUnit.unit',
                'stockAdjustmentInProduct.productUnit.product.images',
            ])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_in_product_serials.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_in_product_serials.stock_adjustment_id')
            ->join('stock_adjustment_in_products', 'stock_adjustment_in_products.id', '=', 'stock_adjustment_in_product_serials.stock_adjustment_in_product_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_in_products.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_adjustment_in_product_serials', $companyId)
            ->whereBranchId('stock_adjustment_in_product_serials', $branchId)
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
            $stockAdjustmentProductName,
            $stockAdjustmentProductCategoryId,
            $stockAdjustmentProductBrandId,
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

            if ($stockAdjustmentProductName) {
                $query->where('products.name', 'like', '%'.$stockAdjustmentProductName.'%');
            }

            if ($stockAdjustmentProductCategoryId) {
                $query->where('products.category_id', $stockAdjustmentProductCategoryId);
            }

            if ($stockAdjustmentProductBrandId) {
                $query->where('products.brand_id', $stockAdjustmentProductBrandId);
            }
        });

        $query->orderBy('stock_adjustments.date', 'desc')
            ->orderBy('stock_adjustment_in_product_serials.id', 'asc');

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
                    empty($stockAdjustmentProductName) ? '[empty]' : $stockAdjustmentProductName,
                    $stockAdjustmentProductCategoryId ?? '[null]',
                    $stockAdjustmentProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_adjustment_in_product_serial_'.implode('_', $cacheParams);

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

    public function create(StockAdjustmentInProductSerialCreateDTO $data): StockAdjustmentInProductSerial
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentInProductSerial = new StockAdjustmentInProductSerial();
            $stockAdjustmentInProductSerial->company_id = $data->companyId;
            $stockAdjustmentInProductSerial->branch_id = $data->branchId;
            $stockAdjustmentInProductSerial->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentInProductSerial->stock_adjustment_in_product_id = $data->stockAdjustmentInProductId;
            $stockAdjustmentInProductSerial->serial = $data->serial;
            $stockAdjustmentInProductSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockAdjustmentInProductSerial(
                stockAdjustmentInProductSerial: $stockAdjustmentInProductSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $stockAdjustmentInProductSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function read(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial): StockAdjustmentInProductSerial
    {
        return $stockAdjustmentInProductSerial->load([
            'company',
            'branch',
            'stockAdjustment',
            'stockAdjustmentInProduct.stockAdjustment',
            'stockAdjustmentInProduct.productUnit.unit',
            'stockAdjustmentInProduct.productUnit.product.images',
            'stockAdjustmentInProduct.stockTransaction',
        ]);
    }

    public function update(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial, StockAdjustmentInProductSerialUpdateDTO $data): StockAdjustmentInProductSerial
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentInProductSerial->serial = $data->serial;
            $stockAdjustmentInProductSerial->save();

            $stockSerialTransaction = $stockAdjustmentInProductSerial->stockSerialTransaction;
            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromStockAdjustmentInProductSerial($stockAdjustmentInProductSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockAdjustmentInProductSerial($stockAdjustmentInProductSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $stockAdjustmentInProductSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockSerialTransaction = $stockAdjustmentInProductSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $retval = $stockAdjustmentInProductSerial->delete();

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
