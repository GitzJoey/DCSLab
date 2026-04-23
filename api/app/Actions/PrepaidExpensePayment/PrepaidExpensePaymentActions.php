<?php

namespace App\Actions\PrepaidExpensePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\PrepaidExpense\PrepaidExpenseActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PrepaidExpensePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PrepaidExpensePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'prepaidExpense.category',
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

        ?int $prepaidExpenseId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = PrepaidExpensePayment::with(self::LIST_EAGER_LOADS)
            ->select('prepaid_expense_payments.*')
            ->whereCompanyId('prepaid_expense_payments', $companyId)
            ->whereBranchId('prepaid_expense_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $prepaidExpenseId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $prepaidExpenseId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('prepaid_expense_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('prepaid_expense_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($prepaidExpenseId)) {
                    $query->where('prepaid_expense_payments.prepaid_expense_id', $prepaidExpenseId);
                }
            });

            if ($includeId) {
                $query->orWhere('prepaid_expense_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(prepaid_expense_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('prepaid_expense_payments.date', 'desc');
        $query->orderBy('prepaid_expense_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $prepaidExpenseId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_prepaid_expense_payment_'.implode('_', $cacheParams);

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

    public function read(PrepaidExpensePayment $prepaidExpensePayment): PrepaidExpensePayment
    {
        return $prepaidExpensePayment->load(self::LIST_EAGER_LOADS);
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
                $count = PrepaidExpensePayment::whereCompanyId('prepaid_expense_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PREP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PrepaidExpensePayment::whereCompanyId('prepaid_expense_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        array $data,
        bool $updateParentSummary = true,
    ): PrepaidExpensePayment {
        $timer_start = microtime(true);

        try {
            $prepaidExpensePayment = new PrepaidExpensePayment();
            $prepaidExpensePayment->company_id = $data['company_id'];
            $prepaidExpensePayment->branch_id = $data['branch_id'];
            $prepaidExpensePayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $prepaidExpensePayment->date = $this->generateDate($data['date']);
            $prepaidExpensePayment->prepaid_expense_id = $data['prepaid_expense_id'];
            $prepaidExpensePayment->cash_account_id = $data['cash_account_id'];
            $prepaidExpensePayment->amount = $data['amount'];
            $prepaidExpensePayment->remarks = $data['remarks'];
            $prepaidExpensePayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPrepaidExpensePayment($prepaidExpensePayment)
            );

            if ($updateParentSummary) {
                PrepaidExpenseActions::updateSummary($prepaidExpensePayment->prepaidExpense);
                $prepaidExpensePayment->refresh();
            }

            $this->flushCache();

            return $prepaidExpensePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PrepaidExpensePayment $prepaidExpensePayment,
        array $data,
        bool $updateParentSummary = true,
    ): PrepaidExpensePayment {
        $timer_start = microtime(true);

        try {
            $prepaidExpensePayment->code = $this->generateUniqueCode($prepaidExpensePayment->company_id, $data['code'], $prepaidExpensePayment->id);
            $prepaidExpensePayment->date = $this->generateDate($data['date']);
            $prepaidExpensePayment->cash_account_id = $data['cash_account_id'];
            $prepaidExpensePayment->amount = $data['amount'];
            $prepaidExpensePayment->remarks = $data['remarks'];
            $prepaidExpensePayment->save();

            $cashTransaction = $prepaidExpensePayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPrepaidExpensePayment($prepaidExpensePayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPrepaidExpensePayment($prepaidExpensePayment)
                );
            }

            if ($updateParentSummary) {
                PrepaidExpenseActions::updateSummary($prepaidExpensePayment->prepaidExpense);
                $prepaidExpensePayment->refresh();
            }

            $this->flushCache();

            return $prepaidExpensePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        PrepaidExpensePayment $prepaidExpensePayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $prepaidExpensePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $retval = $prepaidExpensePayment->delete();

            if ($updateParentSummary) {
                PrepaidExpenseActions::updateSummary($prepaidExpensePayment->prepaidExpense);
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
