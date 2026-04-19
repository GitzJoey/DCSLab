<?php

namespace App\Actions\CapitalOpening;

use App\Actions\CashTransaction\CashTransactionActions;
use App\DTOs\CapitalOpeningCreateDTO;
use App\DTOs\CapitalOpeningUpdateDTO;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\CapitalOpening;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CapitalOpeningActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'investor',
        'cashAccount',
    ];

    public function __construct(
        private CashTransactionActions $cashTransactionActions
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?int $investorId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = CapitalOpening::select('capital_openings.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'capital_openings.company_id')
            ->whereCompanyId('capital_openings', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $investorId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('capital_openings.code', 'like', '%'.$search.'%')
                        ->orWhere('capital_openings.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($branchId) {
                $query->where('capital_openings.branch_id', $branchId);
            }

            if ($investorId) {
                $query->where('capital_openings.investor_id', $investorId);
            }

            if ($cashAccountId) {
                $query->where('capital_openings.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('capital_openings.date', 'desc')
            ->orderBy('capital_openings.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $investorId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_capital_opening_'.implode('_', $cacheParams);

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

    public function read(CapitalOpening $capitalOpening): CapitalOpening
    {
        return $capitalOpening->load(self::LIST_EAGER_LOADS);
    }

    public function create(CapitalOpeningCreateDTO $data): CapitalOpening
    {
        $timer_start = microtime(true);

        try {
            $capitalOpening = new CapitalOpening();
            $capitalOpening->company_id = $data->companyId;
            $capitalOpening->branch_id = $data->branchId;
            $capitalOpening->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $capitalOpening->date = $data->date;
            $capitalOpening->investor_id = $data->investorId;
            $capitalOpening->cash_account_id = $data->cashAccountId;
            $capitalOpening->amount = $data->amount;
            $capitalOpening->remarks = $data->remarks;
            $capitalOpening->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromCapitalOpening($capitalOpening)
            );

            $this->flushCache();

            return $capitalOpening;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CapitalOpening $capitalOpening, CapitalOpeningUpdateDTO $data): CapitalOpening
    {
        $timer_start = microtime(true);

        try {
            $capitalOpening->code = $this->generateUniqueCode($capitalOpening->company_id, $data->code, $capitalOpening->id);
            $capitalOpening->date = $data->date;
            $capitalOpening->investor_id = $data->investorId;
            $capitalOpening->cash_account_id = $data->cashAccountId;
            $capitalOpening->amount = $data->amount;
            $capitalOpening->remarks = $data->remarks;
            $capitalOpening->save();

            $cashTransaction = $capitalOpening->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromCapitalOpening($capitalOpening)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromCapitalOpening($capitalOpening)
                );
            }

            $this->flushCache();

            return $capitalOpening->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CapitalOpening $capitalOpening): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $capitalOpening->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $retval = $capitalOpening->delete();

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

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = CapitalOpening::whereCompanyId('capital_openings', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'CO'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = CapitalOpening::whereCompanyId('capital_openings', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
