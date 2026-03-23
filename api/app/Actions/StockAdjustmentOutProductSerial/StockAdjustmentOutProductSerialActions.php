<?php

namespace App\Actions\StockAdjustmentOutProductSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentOutProductSerialCreateDTO;
use App\DTOs\StockAdjustmentOutProductSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockAdjustmentOutProductSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentOutProductSerialActions
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
        ?string $stockAdjustmentProductUnitProductName,
        ?int $stockAdjustmentProductUnitProductCategoryId,
        ?int $stockAdjustmentProductUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentOutProductSerial::select('stock_adjustment_out_product_serials.*')
            ->with([
                'company',
                'branch',
                'stockAdjustmentOutProduct.stockAdjustment',
                'stockAdjustmentOutProduct.productUnit.unit',
                'stockAdjustmentOutProduct.productUnit.product.category',
                'stockAdjustmentOutProduct.productUnit.product.brand',
                'stockAdjustmentOutProduct.productUnit.product.images',
            ])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_out_product_serials.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_out_product_serials.stock_adjustment_id')
            ->join('stock_adjustment_out_products', 'stock_adjustment_out_products.id', '=', 'stock_adjustment_out_product_serials.stock_adjustment_out_product_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_out_products.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_adjustment_out_product_serials', $companyId)
            ->whereBranchId('stock_adjustment_out_product_serials', $branchId)
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
            ->orderBy('stock_adjustment_out_product_serials.id', 'asc');

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

                $cacheKey = 'read_any_stock_adjustment_out_product_serial_'.implode('_', $cacheParams);

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

    public function read(StockAdjustmentOutProductSerial $stockAdjustmentOutProductSerial): StockAdjustmentOutProductSerial
    {
        return $stockAdjustmentOutProductSerial->load([
            'company',
            'branch',
            'stockAdjustment',
            'stockAdjustmentOutProduct',
            'stockAdjustmentOutProduct.stockAdjustment',
            'stockAdjustmentOutProduct.productUnit.unit',
            'stockAdjustmentOutProduct.productUnit.product.images',
        ]);
    }

    public function create(StockAdjustmentOutProductSerialCreateDTO $data): StockAdjustmentOutProductSerial
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutProductSerial = new StockAdjustmentOutProductSerial();
            $stockAdjustmentOutProductSerial->company_id = $data->companyId;
            $stockAdjustmentOutProductSerial->branch_id = $data->branchId;
            $stockAdjustmentOutProductSerial->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentOutProductSerial->stock_adjustment_out_product_id = $data->stockAdjustmentOutProductId;
            $stockAdjustmentOutProductSerial->serial = $data->serial;
            $stockAdjustmentOutProductSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockAdjustmentOutProductSerial(
                stockAdjustmentOutProductSerial: $stockAdjustmentOutProductSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $stockAdjustmentOutProductSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockAdjustmentOutProductSerial $stockAdjustmentOutProductSerial, StockAdjustmentOutProductSerialUpdateDTO $data): StockAdjustmentOutProductSerial
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutProductSerial->serial = $data->serial;
            $stockAdjustmentOutProductSerial->save();

            $stockSerialTransaction = $stockAdjustmentOutProductSerial->stockSerialTransaction;

            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromStockAdjustmentOutProductSerial($stockAdjustmentOutProductSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockAdjustmentOutProductSerial($stockAdjustmentOutProductSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $stockAdjustmentOutProductSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentOutProductSerial $stockAdjustmentOutProductSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockSerialTransaction = $stockAdjustmentOutProductSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $retval = $stockAdjustmentOutProductSerial->delete();

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
