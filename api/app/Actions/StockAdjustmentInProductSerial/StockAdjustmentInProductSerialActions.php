<?php

namespace App\Actions\StockAdjustmentInProductSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentInProductSerialCreateDTO;
use App\DTOs\StockAdjustmentInProductSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
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
        ?int $stockAdjustmentId,
        ?int $productId,
        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentInProductSerial::select('stock_adjustment_in_product_serials.*')
            ->with(['company', 'branch', 'stockAdjustment', 'stockAdjustmentInProduct'])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_in_product_serials.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_in_product_serials.stock_adjustment_id')
            ->join('stock_adjustment_in_products', 'stock_adjustment_in_products.id', '=', 'stock_adjustment_in_product_serials.stock_adjustment_in_product_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_in_products.product_unit_id')
            ->whereCompanyId('stock_adjustment_in_product_serials', $companyId)
            ->whereBranchId($branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $stockAdjustmentId, $productId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($stockAdjustmentId) {
                $query->where('stock_adjustment_id', $stockAdjustmentId);
            }

            if ($productId) {
                $query->where('product_units.product_id', $productId);
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
                    $stockAdjustmentId ?? '[null]',
                    $productId ?? '[null]',
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
        return $stockAdjustmentInProductSerial->load(['company', 'branch', 'stockAdjustment', 'stockAdjustmentInProduct']);
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
