<?php

namespace App\Actions\StockTransaction;

use App\DTOs\ExecuteDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Models\StockTransaction;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockTransactionActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'warehouse',
        'product',
        'referable',
    ];

    public function __construct()
    {
    }

    public function readAny(
        ?string $referableType,
        ?int $referableId,
        ?int $warehouseId,
        ?int $productId,

        ?ExecuteDTO $execute
    ) {
        $query = StockTransaction::select('stock_transactions.*')
            ->with(self::LIST_EAGER_LOADS);

        $query->where(function ($query) use ($referableType, $referableId, $warehouseId, $productId) {
            if ($referableType) {
                $query->where('stock_transactions.referable_type', $referableType);
            }

            if ($referableId) {
                $query->where('stock_transactions.referable_id', $referableId);
            }

            if ($warehouseId) {
                $query->where('stock_transactions.warehouse_id', $warehouseId);
            }

            if ($productId) {
                $query->where('stock_transactions.product_id', $productId);
            }
        });

        $query->orderBy('stock_transactions.date', 'desc')
            ->orderBy('stock_transactions.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $referableType ?? '[null]',
                    $referableId ?? '[null]',
                    $warehouseId ?? '[null]',
                    $productId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_transaction_'.implode('_', $cacheParams);

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

    public function read(StockTransaction $stockTransaction): StockTransaction
    {
        return $stockTransaction->load(self::LIST_EAGER_LOADS);
    }

    public function create(StockTransactionCreateDTO $data): StockTransaction
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = new StockTransaction();
            $stockTransaction->referable_type = $data->referableType;
            $stockTransaction->referable_id = $data->referableId;
            $stockTransaction->date = $data->date;
            $stockTransaction->warehouse_id = $data->warehouseId;
            $stockTransaction->product_id = $data->productId;
            $stockTransaction->base_qty = $data->baseQty;
            $stockTransaction->save();

            $this->flushCache();

            return $stockTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockTransaction $stockTransaction, StockTransactionUpdateDTO $data): StockTransaction
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction->referable_type = $data->referableType;
            $stockTransaction->referable_id = $data->referableId;
            $stockTransaction->date = $data->date;
            $stockTransaction->warehouse_id = $data->warehouseId;
            $stockTransaction->product_id = $data->productId;
            $stockTransaction->base_qty = $data->baseQty;
            $stockTransaction->save();

            $this->flushCache();

            return $stockTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockTransaction $stockTransaction): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $stockTransaction->delete();

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
