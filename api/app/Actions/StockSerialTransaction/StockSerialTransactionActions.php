<?php

namespace App\Actions\StockSerialTransaction;

use App\DTOs\ExecuteDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Models\StockSerialTransaction;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockSerialTransactionActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        ?string $referableType,
        ?int $referableId,
        ?int $warehouseId,
        ?int $productId,
        ?string $serial,
        ?ExecuteDTO $execute
    ) {
        $query = StockSerialTransaction::select('stock_serial_transactions.*')
            ->with(['warehouse', 'product', 'referable']);

        $query->where(function ($query) use ($referableType, $referableId, $warehouseId, $productId, $serial) {
            if ($referableType) {
                $query->where('stock_serial_transactions.referable_type', $referableType);
            }

            if ($referableId) {
                $query->where('stock_serial_transactions.referable_id', $referableId);
            }

            if ($warehouseId) {
                $query->where('stock_serial_transactions.warehouse_id', $warehouseId);
            }

            if ($productId) {
                $query->where('stock_serial_transactions.product_id', $productId);
            }

            if ($serial) {
                $query->where('stock_serial_transactions.serial', $serial);
            }
        });

        $query->orderBy('stock_serial_transactions.date', 'desc')
            ->orderBy('stock_serial_transactions.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $referableType ?? '[null]',
                    $referableId ?? '[null]',
                    $warehouseId ?? '[null]',
                    $productId ?? '[null]',
                    $serial ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_serial_transaction_'.implode('_', $cacheParams);

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

    public function read(StockSerialTransaction $stockSerialTransaction): StockSerialTransaction
    {
        return $stockSerialTransaction->load(['warehouse', 'product', 'referable']);
    }

    public function create(StockSerialTransactionCreateDTO $data): StockSerialTransaction
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction = new StockSerialTransaction();
            $stockSerialTransaction->referable_type = $data->referableType;
            $stockSerialTransaction->referable_id = $data->referableId;
            $stockSerialTransaction->date = $data->date;
            $stockSerialTransaction->warehouse_id = $data->warehouseId;
            $stockSerialTransaction->product_id = $data->productId;
            $stockSerialTransaction->direction = $data->direction;
            $stockSerialTransaction->serial = $data->serial;
            $stockSerialTransaction->save();

            $this->flushCache();

            return $stockSerialTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockSerialTransaction $stockSerialTransaction, StockSerialTransactionUpdateDTO $data): StockSerialTransaction
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction->referable_type = $data->referableType;
            $stockSerialTransaction->referable_id = $data->referableId;
            $stockSerialTransaction->date = $data->date;
            $stockSerialTransaction->warehouse_id = $data->warehouseId;
            $stockSerialTransaction->product_id = $data->productId;
            $stockSerialTransaction->direction = $data->direction;
            $stockSerialTransaction->serial = $data->serial;
            $stockSerialTransaction->save();

            $this->flushCache();

            return $stockSerialTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockSerialTransaction $stockSerialTransaction): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $stockSerialTransaction->delete();

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
