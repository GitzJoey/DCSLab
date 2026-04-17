<?php

namespace App\Actions\CashTransaction;

use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\CashTransaction;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CashTransactionActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'cashAccount',
        'referable',
    ];

    public function __construct()
    {
    }

    public function readAny(
        ?string $referableType,
        ?int $referableId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = CashTransaction::select('cash_transactions.*')
            ->with(self::LIST_EAGER_LOADS);

        $query->where(function ($query) use ($referableType, $referableId, $cashAccountId) {
            if ($referableType) {
                $query->where('cash_transactions.referable_type', $referableType);
            }

            if ($referableId) {
                $query->where('cash_transactions.referable_id', $referableId);
            }

            if ($cashAccountId) {
                $query->where('cash_transactions.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('cash_transactions.date', 'desc')
            ->orderBy('cash_transactions.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $referableType ?? '[null]',
                    $referableId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_cash_transaction_'.implode('_', $cacheParams);

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

    public function read(CashTransaction $cashTransaction): CashTransaction
    {
        return $cashTransaction->load(self::LIST_EAGER_LOADS);
    }

    public function create(CashTransactionCreateDTO $data): CashTransaction
    {
        $timer_start = microtime(true);

        try {
            $cashTransaction = new CashTransaction();
            $cashTransaction->referable_type = $data->referableType;
            $cashTransaction->referable_id = $data->referableId;
            $cashTransaction->date = $data->date;
            $cashTransaction->cash_account_id = $data->cashAccountId;
            $cashTransaction->amount = $data->amount;
            $cashTransaction->save();

            $this->flushCache();

            return $cashTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CashTransaction $cashTransaction, CashTransactionUpdateDTO $data): CashTransaction
    {
        $timer_start = microtime(true);

        try {
            $cashTransaction->referable_type = $data->referableType;
            $cashTransaction->referable_id = $data->referableId;
            $cashTransaction->date = $data->date;
            $cashTransaction->cash_account_id = $data->cashAccountId;
            $cashTransaction->amount = $data->amount;
            $cashTransaction->save();

            $this->flushCache();

            return $cashTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CashTransaction $cashTransaction): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $cashTransaction->delete();

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
