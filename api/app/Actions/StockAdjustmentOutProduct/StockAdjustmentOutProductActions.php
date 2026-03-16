<?php

namespace App\Actions\StockAdjustmentOutProduct;

use App\Actions\StockAdjustmentOutProductSerial\StockAdjustmentOutProductSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentOutProductCreateDTO;
use App\DTOs\StockAdjustmentOutProductUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Models\StockAdjustmentOutProduct;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentOutProductActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockAdjustmentOutProductSerialActions;

    private $stockTransactionActions;

    public function __construct(
        StockTransactionActions $stockTransactionActions,
        StockAdjustmentOutProductSerialActions $stockAdjustmentOutProductSerialActions,
    ) {
        $this->stockTransactionActions = $stockTransactionActions;
        $this->stockAdjustmentOutProductSerialActions = $stockAdjustmentOutProductSerialActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $stockAdjustmentId,
        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentOutProduct::select('stock_adjustment_out_products.*')
            ->with(['company', 'branch', 'productUnit'])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_out_products.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_out_products.stock_adjustment_id')
            ->whereCompanyId('stock_adjustment_out_products', $companyId)
            ->whereBranchId($branchId)
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
            ->orderBy('stock_adjustment_out_products.id', 'asc');

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

                $cacheKey = 'read_any_stock_adjustment_out_product_'.implode('_', $cacheParams);

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

    public function read(StockAdjustmentOutProduct $stockAdjustmentOutProduct): StockAdjustmentOutProduct
    {
        return $stockAdjustmentOutProduct->load(['company', 'branch', 'productUnit']);
    }

    public function create(StockAdjustmentOutProductCreateDTO $data): StockAdjustmentOutProduct
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutProduct = new StockAdjustmentOutProduct();
            $stockAdjustmentOutProduct->company_id = $data->companyId;
            $stockAdjustmentOutProduct->branch_id = $data->branchId;
            $stockAdjustmentOutProduct->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentOutProduct->qty = $data->qty;
            $stockAdjustmentOutProduct->product_unit_id = $data->productUnitId;
            $stockAdjustmentOutProduct->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentOutProduct->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockAdjustmentOutProduct->remarks = $data->remarks;
            $stockAdjustmentOutProduct->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockAdjustmentOutProduct($stockAdjustmentOutProduct)
            );

            foreach ($data->serials as $serial) {
                $this->stockAdjustmentOutProductSerialActions->create($stockAdjustmentOutProduct, $serial);
            }

            $this->flushCache();

            return $stockAdjustmentOutProduct;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockAdjustmentOutProduct $stockAdjustmentOutProduct, StockAdjustmentOutProductUpdateDTO $data): StockAdjustmentOutProduct
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutProduct->qty = $data->qty;
            $stockAdjustmentOutProduct->product_unit_id = $data->productUnitId;
            $stockAdjustmentOutProduct->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentOutProduct->product_unit_qty_base = $stockAdjustmentOutProduct->qty * $stockAdjustmentOutProduct->product_unit_conversion_value;
            $stockAdjustmentOutProduct->remarks = $data->remarks;
            $stockAdjustmentOutProduct->save();

            $stockTransaction = $stockAdjustmentOutProduct->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromStockAdjustmentOutProduct($stockAdjustmentOutProduct);
                $stockTransaction = $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockAdjustmentOutProduct($stockAdjustmentOutProduct);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $stockAdjustmentOutProductSerial = $stockAdjustmentOutProduct->serials()->findOrFail($deleteId);
                $this->stockAdjustmentOutProductSerialActions->delete($stockAdjustmentOutProductSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $stockAdjustmentOutProductSerial = $stockAdjustmentOutProduct->serials()->findOrFail($serial['id']);
                    $this->stockAdjustmentOutProductSerialActions->update($stockAdjustmentOutProductSerial, $serial['serial']);
                } else {
                    $this->stockAdjustmentOutProductSerialActions->create($stockAdjustmentOutProduct, $serial['serial']);
                }
            }

            $this->flushCache();

            return $stockAdjustmentOutProduct;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentOutProduct $stockAdjustmentOutProduct): bool
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = $stockAdjustmentOutProduct->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            $serials = $stockAdjustmentOutProduct->serials;
            foreach ($serials as $serial) {
                $this->stockAdjustmentOutProductSerialActions->delete($serial);
            }

            $result = $stockAdjustmentOutProduct->delete();

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
