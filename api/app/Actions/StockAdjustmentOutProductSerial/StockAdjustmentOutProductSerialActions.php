<?php

namespace App\Actions\StockAdjustmentOutProductSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentOutProductSerialCreateDTO;
use App\DTOs\StockAdjustmentOutProductSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
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
        ?int $stockAdjustmentId,
        ?int $productId,
        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentOutProductSerial::select('stock_adjustment_out_product_serials.*')
            ->with(['company', 'branch', 'stockAdjustment', 'stockAdjustmentOutProduct'])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_out_product_serials.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_out_product_serials.stock_adjustment_id')
            ->join('stock_adjustment_out_products', 'stock_adjustment_out_products.id', '=', 'stock_adjustment_out_product_serials.stock_adjustment_out_product_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_out_products.product_unit_id')
            ->whereCompanyId('stock_adjustment_out_product_serials', $companyId)
            ->whereBranchId($branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $productId, $stockAdjustmentId) {
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
                    $stockAdjustmentId ?? '[null]',
                    $productId ?? '[null]',
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
        return $stockAdjustmentOutProductSerial->load(['company', 'branch', 'stockAdjustment', 'stockAdjustmentOutProduct']);
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
