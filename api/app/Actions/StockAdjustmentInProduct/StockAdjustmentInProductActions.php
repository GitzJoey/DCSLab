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
        ?int $stockAdjustmentId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentInProduct::select('stock_adjustment_in_products.*')
            ->with(['company', 'branch', 'productUnit'])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_in_products.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_in_products.stock_adjustment_id')
            ->whereCompanyId('stock_adjustment_in_products', $companyId)
            ->whereBranchId('stock_adjustment_in_products', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $stockAdjustmentId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($stockAdjustmentId) {
                $query->where('stock_adjustment_id', $stockAdjustmentId);
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
                    $stockAdjustmentId ?? '[null]',
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
        return $stockAdjustmentInProduct->load(['company', 'branch', 'productUnit']);
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
                $dto = StockAdjustmentInProductSerialCreateDTO::fromStockAdjustmentInProduct($stockAdjustmentInProduct, $serial);
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
                    $this->stockAdjustmentInProductSerialActions->create($stockAdjustmentInProductSerial, $dto);
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
