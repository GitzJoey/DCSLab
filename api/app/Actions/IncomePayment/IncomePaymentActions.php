<?php

namespace App\Actions\IncomePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\Income\IncomeActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\IncomePaymentCreateDTO;
use App\DTOs\IncomePaymentUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\IncomePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class IncomePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'income.category',
        'cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $incomeId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = IncomePayment::with(self::LIST_EAGER_LOADS)
            ->select('income_payments.*')
            ->whereCompanyId('income_payments', $companyId)
            ->whereBranchId('income_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $incomeId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $incomeId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('income_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('income_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($incomeId)) {
                    $query->where('income_payments.income_id', $incomeId);
                }
            });

            if ($includeId) {
                $query->orWhere('income_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(income_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('income_payments.date', 'desc');
        $query->orderBy('income_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $incomeId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_income_payment_'.implode('_', $cacheParams);

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

    public function read(IncomePayment $incomePayment): IncomePayment
    {
        return $incomePayment->load(self::LIST_EAGER_LOADS);
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = IncomePayment::whereCompanyId('income_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'INCP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = IncomePayment::whereCompanyId('income_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(IncomePaymentCreateDTO $data, bool $updateParentSummary = true): IncomePayment
    {
        $timer_start = microtime(true);

        try {
            $incomePayment = new IncomePayment();
            $incomePayment->company_id = $data->companyId;
            $incomePayment->branch_id = $data->branchId;
            $incomePayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $incomePayment->date = $this->generateDate($data->date);
            $incomePayment->income_id = $data->incomeId;
            $incomePayment->cash_account_id = $data->cashAccountId;
            $incomePayment->amount = $data->amount;
            $incomePayment->remarks = $data->remarks;
            $incomePayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromIncomePayment($incomePayment)
            );

            if ($updateParentSummary) {
                IncomeActions::updateSummary($incomePayment->income);
                $incomePayment->refresh();
            }

            $this->flushCache();

            return $incomePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(IncomePayment $incomePayment, IncomePaymentUpdateDTO $data, bool $updateParentSummary = true): IncomePayment
    {
        $timer_start = microtime(true);

        try {
            $incomePayment->code = $this->generateUniqueCode($incomePayment->company_id, $data->code, $incomePayment->id);
            $incomePayment->date = $this->generateDate($data->date);
            $incomePayment->cash_account_id = $data->cashAccountId;
            $incomePayment->amount = $data->amount;
            $incomePayment->remarks = $data->remarks;
            $incomePayment->save();

            $cashTransaction = $incomePayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromIncomePayment($incomePayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromIncomePayment($incomePayment)
                );
            }

            if ($updateParentSummary) {
                IncomeActions::updateSummary($incomePayment->income);
                $incomePayment->refresh();
            }

            $this->flushCache();

            return $incomePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        IncomePayment $incomePayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $incomePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $retval = $incomePayment->delete();

            if ($updateParentSummary) {
                IncomeActions::updateSummary($incomePayment->income);
            }

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
